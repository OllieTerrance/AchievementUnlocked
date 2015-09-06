<?
require_once getenv("PHPLIB") . "keystore.php";
mysql_connect(keystore("mysql", "db"), keystore("mysql", "user"), keystore("mysql", "pass"));
mysql_select_db("terrance_labs");
session_start();
$return = null;
$showPage = False;
$act = $_GET["act"];
$tid = $_REQUEST["tid"];
if (!$_SESSION["login"]) {
	header("Location: user.php?act=login");
}
else if ($tid && ($act === "edit" || $act === "delete")) {
	$data = mysql_query("SELECT `uid` FROM `ach__tasks` WHERE `tid` = " . $tid . " AND `uid` = " . $_SESSION["login"]["uid"] . ";");
	$row = mysql_fetch_array($data);
	if (!$row) {
		$return = "Oi!  Task #" . $tid . " isn't yours.  GTFO.  <a href=\"./\">Cancel</a>";
	}
}
if ($_POST) {
	if ($_POST["form_val"] !== "1") {
		$return = "Something went wrong whilst processing that...  uhh, is your JavaScript turned off?";
		$showPage = True;
	}
	else if (!$return) {
		$name = mysql_real_escape_string($_POST["name"]);
		$desc = mysql_real_escape_string($_POST["desc"]);
		$cur = (int) $_POST["cur"];
		$tot = (int) $_POST["tot"];
		if (!$return) {
			switch ($act) {
				case "add":
					if (mysql_query("INSERT INTO `ach__tasks` VALUES (NULL, " . $_SESSION["login"]["uid"] . ", \"" . $name . "\", \"" . $desc . "\", " . $cur . ", " . $tot . ");")) {
						$return = "Successfully added to your achievement list!  <a href=\"./\">Continue</a> or add another...";
						$showPage = True;
					}
					else {
						$return = "<strong>Error:</strong> failed to add, seems there's a problem on our side.  Try again later?<br/><pre>" . mysql_error() . "</pre>";
						$showPage = True;
					}
					break;
				case "edit":
					if (mysql_query("UPDATE `ach__tasks` SET `name` = \"" . $name . "\", `desc` = \"" . $desc . "\", `cur` = " . $cur . ", `tot` = " . $tot . " WHERE `tid` = " . $tid . ";")) {
						$return = "Successfully edited in your achievement list!  <a href=\"./\">Continue</a>";
					}
					else {
						$return = "<strong>Error:</strong> failed to edit, seems there's a problem on our side.  Try again later?<br/><pre>" . mysql_error() . "</pre>";
						$showPage = True;
					}
					break;
				case "delete":
					if (mysql_query("DELETE FROM `ach__tasks` WHERE `tid` = " . $tid . ";")) {
						$return = "Successfully deleted from your achievement list!  <a href=\"./\">Continue</a>";
					}
					else {
						$return = "<strong>Error:</strong> failed to delete, seems there's a problem on our side.  Try again later?<br/><pre>" . mysql_error() . "</pre>";
						$showPage = True;
					}
					break;
			}
		}
	}
}
if (!$return) {
	$tid = (int) $_GET["tid"];
	if (!in_array($act, Array("add", "edit", "delete"))) {
		header("Location: ./");
		die();
	}
	$cur = 0;
	$tot = 1;
	if ($act === "edit" || $act === "delete") {
		$data = mysql_query("SELECT * FROM `ach__tasks` WHERE `tid` = " . $tid . " ORDER BY TRIM(LEADING \"&quot;\" FROM `name`) ASC;");
		$row = mysql_fetch_array($data);
		if ($row) {
			$name = $row["name"];
			$desc = $row["desc"];
			$cur = (int) $row["cur"];
			$tot = (int) $row["tot"];
			$done = ($tot ? $cur : ($cur === $tot));
		}
		else {
			$return = "No task found that matches the ID \"" . $tid . "\".  <a href=\"./\">Cancel</a>";
		}
	}
}
mysql_close();
?><html>
	<head>
		<title>Achievement Unlocked!</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<script src="man.js" type="text/javascript"></script>
	</head>
	<body>
		<div id="outer">
			<div id="inner">
				<h1>
					<a href="./">Achievement Unlocked!</a>
				</h1>
<?
if ($return) {
?>				<div><? print $return; ?></div>
<?
}
if (!$return || $showPage) {
?>
				<form id="form_parent" action="?act=<? print $act; ?>" method="post" onsubmit="return form_submit(<? print ($act === "delete" ? "false" : "true"); ?>);">
<?
	if ($act === "edit" || $act === "delete") {
?>
					<input name="tid" type="hidden" value="<? print $tid; ?>"/>
<?
	}
	if ($act === "add" || $act === "edit") {
?>					<div id="ach" class="ach">
						<input id="name" name="name" placeholder="Task title" value="<? print htmlentities($name); ?>"/>
						<br/>
						<input id="desc" name="desc" placeholder="Short description of the task" value="<? print htmlentities($desc); ?>"/>
						<br/>
						<span id="type_prog"<? print ($tot > 1 ? "" : " style=\"display: none;\""); ?>>
							<input id="cur" name="cur" type="number" min="0" step="1" placeholder="Times achieved" onblur="progress_val(this);" value="<? print $cur; ?>"/>
							<input id="tot" name="tot" type="number" min="1" step="1" placeholder="Times required" onblur="progress_val(this);" value="<? print $tot; ?>"/>
							<button type="button" onclick="return task_type(true);">Switch to checkable type?</button>
						</span>
						<span id="type_check"<? print ($tot > 1 ? " style=\"display: none;\"" : ""); ?>>
							<input id="done" type="checkbox"<? print ($done ? " checked" : ""); ?>/>
							<label for="done">Achieved?</label>
							<button type="button" onclick="return task_type(false);">Switch to progress type?</button>
						</span>
						<input id="form_type" type="hidden" value="<? print ($tot > 1 ? "0" : "1"); ?>"/>
					</div>
					<div>
						<input type="submit" value="<? print ($act === "add" ? "Add" : "Edit"); ?>"/>
						<a href="./">Cancel</a>
					</div>
<?
	}
	else {
?>					<div>
						<strong>Are you sure you want to delete the task <em><? print $name; ?></em>?</strong>
					</div>
					<div>
						<input type="submit" value="Delete"/>
						<a href="./">Cancel</a>
					</div>
<?
	}
	if ($act === "edit" || $act === "delete") {
?>					<input id="form_tid" name="form_tid" type="hidden" value="<? print $tid; ?>"/>
<?
	}
?>					<input id="form_val" name="form_val" type="hidden" value="0"/>
				</form>
<?
}
?>			</div>
		</div>
	</body>

