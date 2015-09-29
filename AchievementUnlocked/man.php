<?
$dbFile = "ach.db";
$db = require_once getenv("PHPLIB") . "db.php";
session_start();
$return = null;
$showPage = False;
$act = $_GET["act"];
$tid = null;
if (in_array($act, array("edit", "delete"))) {
    $tid = $_REQUEST["tid"];
}
if (!$_SESSION["login"]) {
    header("Location: user.php?act=login");
} elseif ($tid && ($act === "edit" || $act === "delete")) {
    $data = $db->select("tasks", "uid", array("AND" => array("tid" => $tid, "uid" => $_SESSION["login"]["uid"])));
    if (count($data) === 0) {
        $return = "Oi!  Task #" . $tid . " isn't yours.  GTFO.  <a href=\"./\">Cancel</a>";
    }
}
if ($_POST) {
    if ($_POST["form_val"] !== "1") {
        $return = "Something went wrong whilst processing that...  uhh, is your JavaScript turned off?";
        $showPage = True;
    } elseif (!$return) {
        if (in_array($act, array("add", "edit"))) {
            $name = $_POST["name"];
            $desc = $_POST["desc"];
            $cur = (int) $_POST["cur"];
            $tot = (int) $_POST["tot"];
        }
        if (!$return) {
            switch ($act) {
                case "add":
                    $db->insert("tasks", array("uid" => $_SESSION["login"]["uid"], "name" => $name, "desc" => $desc, "cur" => $cur, "tot" => $tot));
                    var_dump($db->error());
                    $return = "Successfully added to your achievement list!  <a href=\"./\">Continue</a> or add another...";
                    $showPage = True;
                    break;
                case "edit":
                    $db->update("tasks", array("uid" => $_SESSION["login"]["uid"], "name" => $name, "desc" => $desc, "cur" => $cur, "tot" => $tot), array("tid" => $tid));
                    $return = "Successfully edited in your achievement list!  <a href=\"./\">Continue</a>";
                    break;
                case "delete":
                    $db->delete("tasks", array("tid" => $tid));
                    $return = "Successfully deleted from your achievement list!  <a href=\"./\">Continue</a>";
                    break;
            }
        }
    }
}
$name = "";
$desc = "";
$cur = 0;
$tot = 1;
$done = false;
if (!$return) {
    if (!in_array($act, array("add", "edit", "delete"))) {
        header("Location: ./");
        die();
    }
    if ($act === "edit" || $act === "delete") {
        $tasks = $db->select("tasks", "*", array("tid" => $tid));
        if (count($tasks)) {
            $row = $tasks[0];
            $name = $row["name"];
            $desc = $row["desc"];
            $cur = (int) $row["cur"];
            $tot = (int) $row["tot"];
            $done = ($tot ? $cur : ($cur === $tot));
        } else {
            $return = "No task found that matches the ID \"" . $tid . "\".  <a href=\"./\">Cancel</a>";
        }
    }
}
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
?>
                <div><? print $return; ?></div>
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
?>
                    <div id="ach" class="ach">
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
    } else {
?>
                    <div>
                        <strong>Are you sure you want to delete the task <em><? print $name; ?></em>?</strong>
                    </div>
                    <div>
                        <input type="submit" value="Delete"/>
                        <a href="./">Cancel</a>
                    </div>
<?
    }
    if ($act === "edit" || $act === "delete") {
?>
                    <input id="form_tid" name="form_tid" type="hidden" value="<? print $tid; ?>"/>
<?
    }
?>
                    <input id="form_val" name="form_val" type="hidden" value="0"/>
                </form>
<?
}
?>
            </div>
            <div id="footer">A great achievement of <a href="//terrance.allofti.me">Ollie Terrance</a>'s.</div>
        </div>
    </body>

