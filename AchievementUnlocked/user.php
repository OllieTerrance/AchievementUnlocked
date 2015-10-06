<?
$act = $_GET["act"];
if (!in_array($act, Array("login", "logout", "register", "password"))) {
    header("Location: ./");
    die();
}
$dbFile = "ach.db";
$db = require_once getenv("PHPLIB") . "db.php";
session_start();
$return = null;
$showPage = False;
if ($act === "logout") {
    if (array_key_exists("login", $_SESSION)) {
        session_destroy();
        $return = "You have successfully logged out!  <a href=\"./\">Continue</a>";
    } else {
        $return = "Umm...  you're weren't logged in anyway?  <a href=\"./\">Continue</a>";
    }
} elseif ($act === "register" && array_key_exists("login", $_SESSION)) {
    $return = "<strong>Error:</strong> you're already logged in.  You don't need two accounts, stop being greedy.  <a href=\"./\">Cancel</a>";
} elseif ($act === "password" && !array_key_exists("login", $_SESSION)) {
    header("Location: ?act=login");
}
if ($_POST) {
    switch ($act) {
        case "login":
            $user = $_POST["user"];
            $pass = md5($_POST["pass"]);
            $data = $db->select("users", "*", array("AND" => array("user" => $user, "pass" => $pass)));
            if (count($data)) {
                $row = $data[0];
                $_SESSION["login"] = array("uid" => $row["uid"], "user" => $row["user"]);
                $return = "You have successfully logged in!  <a href=\"./\">Continue</a>";
            } else {
                $return = "<strong>Error:</strong> failed to login.  Is your password correct?  Is your username correct?  Is the square root of -1 real?";
                $showPage = True;
            }
            break;
        case "register":
            $user = $_POST["user"];
            $pass = $_POST["pass"];
            $math1 = $_POST["math1"];
            $math2 = $_POST["math2"];
            $hash = $_POST["hash"];
            if ($hash !== md5($math1 . ":" . $math2)) {
                $return = "<strong>Error:</strong> your ability to complete maths sums is terrible.  Learn to count.";
                $showPage = True;
            } elseif (!$user || !$pass) {
                $return = "<strong>Error:</strong> you must fill in all of the fields.  If you enable your JavaScript, you'd already have known this.";
                $showPage = True;
            } else {
                $data = $db->select("users", array("user" => $user));
                if (count($data)) {
                    $return = "<strong>Error:</strong> a user with this name already exists.  Is it you?  If not, stop stealing names.";
                    $showPage = True;
                } else {
                    $db->insert("users", array("user" => $user, "pass" => md5($pass)));
                    $uid = $db->select("users", "uid", array("user" => $user));
                    $_SESSION["login"] = Array("uid" => $uid, "user" => $user);
                    $return = "You have successfully registered and are now logged in!  <a href=\"./\">Continue</a>";
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
            } elseif ($new !== $conf) {
                $return = "<strong>Error:</strong> the two passwords don't match.  If you enable your JavaScript, you'd already have known this.";
                $showPage = True;
            } else {
                $data = $db->select("users", array("AND" => array("uid" => $_SESSION["login"]["uid"], "pass" => md5($old))));
                if (count($data)) {
                    $db->update("users", array("pass" => md5($new)), array("uid" => $_SESSION["login"]["uid"]));
                    $return = "You have successfully changed your password!  <a href=\"./\">Continue</a>";
                } else {
                    $return = "<strong>Error:</strong> you got your password wrong.  I know you're here to change it and everything, but you should still know the old one.";
                    $showPage = True;
                }
            }
            break;
    }
}
?><html>
    <head>
        <title><? print ucfirst($act); ?> &ndash; Achievement Unlocked!</title>
        <meta name="author" content="Ollie Terrance">
        <meta name="description" content="Oh, achievements.  You know what I'm on about.  You're playing those games, and you'll unlock achievements for the most ridiculous things, easy or not.">
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
?>
                <div><? print $return; ?></div>
<?
}
if (!$return || $showPage) {
    if ($act === "login" || $act === "register") {
        $maths = Array(rand(3, 9), rand(3, 9), rand(2, 7), rand(2, 7));
        $sums = Array($maths[0] . " + " . $maths[1] . " =", $maths[2] . " x " . $maths[3] . " =");
        $hash = md5(($maths[0] + $maths[1]) . ":" . ($maths[2] * $maths[3]));
        if ($act === "login") {
?>
                <div>Login to manage your own achievement list.  Haven't signed up yet?  Go <a href="./user.php?act=register">register</a>!</div>
<?
        } else {
?>
                <div>Register an account to start making your own achievement list.  Your username will be shown on your public list, so choose wisely.  <strong>Warning:</strong> there is currently no way to reset your password, so try to remember it well.  Already signed up?  Go <a href="./user.php?act=login">login</a>.</div>
<?
        }
?>
                <form id="form_parent" action="?act=<? print $act; ?>" method="post" onsubmit="return form_submit_up();">
                    <div>
                        <input id="user" name="user" maxlength="32" placeholder="Username"/>
                        <br/>
                        <input id="pass" name="pass" type="password" placeholder="Password"/>
<?
        if ($act === "register") {
?>
                        <br/>
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
<?
        }
?>
                    </div>
                    <div>
                        <input type="submit" value="<? print ($act === "login" ? "Login" : "Register"); ?>"/>
                        <a href="./">Cancel</a>
                    </div>
                </form>
<?
    } elseif ($act === "password") {
?>
                <div>You can use this page to change your password.</div>
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
?>
            </div>
            <div id="footer">A great achievement of <a href="//terrance.allofti.me">Ollie Terrance</a>'s.</div>
        </div>
    </body>
</html>
