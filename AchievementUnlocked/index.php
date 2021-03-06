<?
$dbFile = "ach.db";
$db = require_once getenv("PHPLIB") . "db.php";
session_start();
$edit = False;
$uid = null;
$user = null;
if (array_key_exists("uid", $_GET) && ctype_digit($_GET["uid"])) {
    $uid = $_GET["uid"];
    $data = $db->select("users", "user", array("uid" => $uid));
    if (count($data)) {
        $user = $data[0];
    } else {
        header("Location: ./");
        die();
    }
} elseif (array_key_exists("login", $_SESSION)) {
    $uid = $_SESSION["login"]["uid"];
    $edit = True;
}
$metaTitle = "";
$metaDesc = "";
if ($uid) {
    $metaTitle = ($user ? $user . "'s" : "Your") . " achievements &ndash; ";
    $metaDesc = "Achievement list for " . $user . ".  ";
    $tasks = $db->select("tasks", "*", array("uid" => $uid));
    function namesort($a, $b) {
        return strnatcmp($a["name"], $b["name"]);
    }
    usort($tasks, "namesort");
}
?><html>
    <head>
        <title><? print $metaTitle; ?>Achievement Unlocked!</title>
        <meta name="author" content="Ollie Terrance">
        <meta name="description" content="<? print $metaDesc; ?>Oh, achievements.  You know what I'm on about.  You're playing those games, and you'll unlock achievements for the most ridiculous things, easy or not.">
        <link href="style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="outer">
            <div id="inner">
                <h1>
                    <a href="./">Achievement Unlocked!</a>
                </h1>
<?
if (array_key_exists("login", $_SESSION)) {
?>
                <div>Logged in as <strong><? print $_SESSION["login"]["user"]; ?></strong>.  <a href="?uid=<? print $_SESSION["login"]["uid"]; ?>">View</a> / <a href="./">Edit</a> Achievements  |  <a href="img.php?uid=<? print $_SESSION["login"]["uid"]; ?>" onclick="prompt('Use this link on forums, websites or anywhere else that supports external images.', this.href); return false;">Random Image Link</a>  |  <a href="user.php?act=password">Change Password</a> / <a href="user.php?act=logout">Logout</a></div>
<?
} else {
?>
                <div>Not logged in.  <a href="user.php?act=login">Login</a> / <a href="user.php?act=register">Register</a></div>
<?
}
if (!$uid && !array_key_exists("login", $_SESSION)) {
?>
                <div>Oh, achievements.  You know what I'm on about.  You're playing those games, and you'll unlock achievements for the most ridiculous things, easy or not.  And these days, you can say "achievement unlocked!" to anything that is mildly or extremely deserving of it.  Now, <strong>Achievement Unlocked!</strong> allows you to easily create your own public achievement lists and share them.  To see what I'm on about, take a look at <a href="?uid=1">my own list</a>.</div>
<?
} else {
    if ($edit) {
?>
                <div>This is <strong>your</strong> achievement list.  Use this page to add, edit and remove tasks on your list.  To share your list with others, use the <a href="?uid=<? print $_SESSION["login"]["uid"]; ?>">public link</a>.</div>
<?
    } else {
?>
                <div>This is the achievement list for the user <strong><? print $user; ?></strong>.</div>
<?
    }
    $allDone = 0;
    $allTot = 0;
    foreach ($tasks as $params) {
        $tid = (int) $params["tid"];
        $name = $params["name"];
        $desc = $params["desc"];
        $cur = (int) $params["cur"];
        $tot = (int) $params["tot"];
        if ($tot > 1) {
            $done = ($cur === $tot ? 1 : 0);
        }
        else {
            $done = $cur;
            $cur = 0;
        }
?>
                <div class="ach <? print ($done ? "y" : "n"); ?>">
                    <div class="box"></div>
<?
        if ($edit) {
?>
                    <span style="float: right;">
                        <a href="man.php?act=edit&tid=<? print $tid; ?>">Edit</a>
                        <a href="man.php?act=delete&tid=<? print $tid; ?>">Delete</a>
                        <a href="img.php?tid=<? print $tid; ?>" onclick="prompt('Use this link on forums, websites or anywhere else that supports external images.', this.href); return false;">Image Link</a>
                    </span>
<?
        }
?>
                    <h3><? print $name; ?></h3>
                    <span><? print ($desc ? $desc : "&nbsp;"); ?></span>
<?
        if ($tot > 1) {
?>
                    <span class="prog">Progress: <? print $cur; ?> / <? print $tot; ?></span>
                    <div class="bar">
                        <div style="width: <? print round(($cur / $tot) * 100); ?>%;"></div>
                    </div>
<?
        }
?>
                </div>
<?
        if ($done) {
            $allDone++;
        }
        $allTot++;
    }
    if ($edit) {
?>
                    <div style="float: right;">
                        <a href="man.php?act=add">Add</a>
                    </div>
<?
    }
    if ($allTot > 0) {
?>
                <div>Completed achievements: <? print $allDone . " / " . $allTot . " (" . round(($allDone / $allTot) * 100) . "%)"; ?></div>
<?
    } else {
?>
                <div>No achievements have been made yet...</div>
<?
    }
}
?>
            </div>
            <div id="footer">A great achievement of <a href="//terrance.allofti.me">Ollie Terrance</a>'s.</div>
        </div>
    </body>
</html>
