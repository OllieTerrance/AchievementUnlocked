<?
$act = $_GET["act"];
if (!in_array($act, Array("login", "logout", "register", "password"))) {
	header("Location: ./");
	die();
}
require_once getenv("PHPLIB") . "keystore.php";
mysql_connect(keystore("mysql", "db"), keystore("mysql", "user"), keystore("mysql", "pass"));
mysql_select_db("terrance_labs");
session_start();
$return = null;
$showPage = False;
if ($act === "logout") {
	if (array_key_exists("login", $_SESSION)) {
		session_destroy();
		$return = "You have successfully logged out!  <a href=\"./\">Continue</a>";
	}
	else {
		$return = "Umm...  you're weren't logged in anyway?  <a href=\"./\">Continue</a>";
	}
}
else if ($act === "register" && array_key_exists("login", $_SESSION)) {
	$return = "<strong>Error:</strong> you're already logged in.  You don't need two accounts, stop being greedy.  <a href=\"./\">Cancel</a>";
}
else if ($act === "password" && !array_key_exists("login", $_SESSION)) {
	header("Location: ?act=login");
}
if ($_POST) {
	switch ($act) {
		case "login":
	        $user = mysql_real_escape_string($_POST["user"]);
			$pass = md5($_POST["pass"]);
			$data = mysql_query("SELECT * FROM `ach__users` WHERE `user` = \"" . $user . "\" AND `pass` = \"" . $pass . "\";");
			$row = mysql_fetch_array($data);
			if ($row) {
				$_SESSION["login"] = Array("uid" => $row["uid"],
										   "user" => $row["user"]);
				$return = "You have successfully logged in!  <a href=\"./\">Continue</a>";
			}
			else {
				$return = "<strong>Error:</strong> failed to login.  Is your password correct?  Is your username correct?  Is the square root of -1 real?";
				$showPage = True;
			}
			break;
		case "register":
	        $user = mysql_real_escape_string($_POST["user"]);
	        $pass = $_POST["pass"];
			$email = $_POST["email"];
			$math1 = $_POST["math1"];
			$math2 = $_POST["math2"];
			$hash = $_POST["hash"];
			if ($hash !== md5($math1 . ":" . $math2)) {
				$return = "<strong>Error:</strong> your ability to complete maths sums is terrible.  Learn to count.";
				$showPage = True;
			}
			else if (!$user || !$pass || !$email) {
				$return = "<strong>Error:</strong> you must fill in all of the fields.  If you enable your JavaScript, you'd already have known this.";
				$showPage = True;
			}
			else if (!preg_match("/^[A-Za-z0-9!#$%&'*+-\/=?^_`{|}~]+@([A-Za-z0-9-]+\.)+[A-Za-z]{2,6}$/", $email)) {
				$return = "<strong>Error:</strong> who're you trying to fool?  Proper email address.  Now.  If you enable your JavaScript, you'd already have known this.";
				$showPage = True;
			}
			else {
				$pass = md5($pass);
				$data = mysql_query("SELECT * FROM `ach__users` WHERE `user` = \"" . $user . "\";");
				$row = mysql_fetch_array($data);
				if ($row) {
					$return = "<strong>Error:</strong> a user with this name already exists.  Is it you?  If not, stop stealing names.";
					$showPage = True;
				}
				else if (mysql_query("INSERT INTO `ach__users` VALUES (NULL, \"" . $user . "\", \"" . $pass . "\", \"" . $email . "\");")) {
					$data = mysql_query("SELECT * FROM `ach__users` WHERE `user` = \"" . $user . "\" AND `pass` = \"" . $pass . "\";");
					$row = mysql_fetch_array($data);
					$_SESSION["login"] = Array("uid" => $row["uid"],
											   "user" => $row["user"]);
					$return = "You have successfully registered and are now logged in!  <a href=\"./\">Continue</a>";
				}
				else {
					$return = "<strong>Error:</strong> failed to register, seems there's a problem on our side.  Try again later?<br/><pre>" . mysql_error() . "</pre>";
					$showPage = True;
				}
			}
			break;
		case "password":
			$old = $_POST["old"];
			$new = $_POST["new"];
			$conf = $_POST["conf"];
			if (!$old || !$new || !$conf) {
				$return = "<strong>Error:</strong> you must fill in all of the fields.  If you enable your JavaScript, you'd already have known this.";
				$showPage = True;
			}
			else if ($new !== $conf) {
				$return = "<strong>Error:</strong> the two passwords don't match.  If you enable your JavaScript, you'd already have known this.";
				$showPage = True;
			}
			else {
				$old = md5($old);
				$new = md5($new);
				$data = mysql_query("SELECT * FROM `ach__users` WHERE `uid` = " . $_SESSION["login"]["uid"] . " AND `pass` = \"" . $old . "\";");
				$row = mysql_fetch_array($data);
				if ($row) {
					if (mysql_query("UPDATE `ach__users` SET `pass` = \"" . $new . "\" WHERE `uid` = " . $_SESSION["login"]["uid"] . ";")) {
						$return = "You have successfully changed your password!  <a href=\"./\">Continue</a>";
					}
					else {
						$return = "<strong>Error:</strong> failed to change your password, seems there's a problem on our side.  Try again later?<br/><pre>" . mysql_error() . "</pre>";
						$showPage = True;
					}
				}
				else {
					$return = "<strong>Error:</strong> you got your password wrong.  I know you're here to change it and everything, but you should still know the old one.";
					$showPage = True;
				}
			}
			break;
	}
}
mysql_close();
?><html>
	<head>
		<title>Achievement Unlocked!</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<script src="user.js" type="text/javascript"></script>
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
	if ($act === "login" || $act === "register") {
		$maths = Array(rand(3, 9), rand(3, 9), rand(2, 7), rand(2, 7));
		$sums = Array($maths[0] . " + " . $maths[1] . " =", $maths[2] . " x " . $maths[3] . " =");
		$hash = md5(($maths[0] + $maths[1]) . ":" . ($maths[2] * $maths[3]));
		if ($act === "login") {
?>				<div>Login to manage your own achievement list.  Haven't signed up yet?  Go <a href="./user.php?act=register">register</a>!</div>
<?
		}
		else {
?>				<div>Register an account to start making your own achievement list.  Your username will be shown on your public list, so choose wisely.  <strong>Warning:</strong> there is currently no way to reset your password, so try to remember it well.  Already signed up?  Go <a href="./user.php?act=login">login</a>.</div>
<?
		}
?>				<form id="form_parent" action="?act=<? print $act; ?>" method="post" onsubmit="return form_submit_up();">
					<div>
						<input id="user" name="user" maxlength="32" placeholder="Username"/>
						<br/>
						<input id="pass" name="pass" type="password" placeholder="Password"/>
<?		if ($act === "register") {
?>						<br/>
						<input id="email" name="email" placeholder="Email address"/>
					</div>
					<div class="ach">
						<em>Maths test: prove you're a human!</em>
						<br/>
						<code id="sum1"><? print $sums[0]; ?></code>
						<input id="math1" name="math1" type="number" min="0" step="1" value="0"/>
						<br/>
						<code id="sum2"><? print $sums[1]; ?></code>
						<input id="math2" name="math2" type="number" min="0" step="1" value="0"/>
						<input type="hidden" name="hash" value="<? print $hash; ?>"/>
<?		}
?>					</div>
					<div>
						<input type="submit" value="<? print ($act === "login" ? "Login" : "Register"); ?>"/>
						<a href="./">Cancel</a>
					</div>
				</form>
<?
	}
	else if ($act === "password") {
?>				<div>You can use this page to change your password.</div>
				<form id="form_parent" action="?act=<? print $act; ?>" method="post" onsubmit="return form_submit_pp();">
					<div>
						<input id="old" name="old" type="password" placeholder="Current password"/>
						<br/>
						<input id="new" name="new" type="password" placeholder="New password"/>
						<br/>
						<input id="conf" name="conf" type="password" placeholder="Confirm new password"/>
					</div>
					<div>
						<input type="submit" value="Change"/>
						<a href="./">Cancel</a>
					</div>
				</form>
<?
	}
}
?>			</div>
			<div id="footer">Created by <a href="/">Ollie Terrance</a>.  Back to <a href="/labs/">Terrance Laboratories</a>.</div>
		</div>
	</body>
</html>
