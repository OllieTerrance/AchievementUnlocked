<?
$dbFile = "ach.db";
$db = require_once getenv("PHPLIB") . "db.php";
session_start();
if (array_key_exists("tid", $_GET)) {
    $tid = $_GET["tid"];
    $tasks = $db->select("tasks", "*", array("tid" => $tid));
    if (count($tasks) === 0) {
        die("No task found with the ID " . $tid . ".");
    }
    $row = $tasks[0];
    $name = $row["name"];
    $desc = $row["desc"];
    $cur = (int) $row["cur"];
    $tot = (int) $row["tot"];
    if ($tot > 1) {
        $done = ($cur === $tot ? 1 : 0);
    } else {
        $done = $cur;
        $cur = 0;
    }
} elseif (array_key_exists("uid", $_GET)) {
    $uid = $_GET["uid"];
    $tasks = $db->select("tasks", "*", array("uid" => $uid));
    if (count($tasks) === 0) {
        die("No tasks found for the user with ID " . $uid . ".");
    }
    $row = $tasks[rand(0, count($tasks) - 1)];
    $name = $row["name"];
    $desc = $row["desc"];
    $cur = (int) $row["cur"];
    $tot = (int) $row["tot"];
    if ($tot > 1) {
        $done = ($cur === $tot ? 1 : 0);
    } else {
        $done = $cur;
        $cur = 0;
    }
} else {
    header("./");
}
$img = imagecreatefrompng("res/" . ($done ? "y" : "n") . "_" . ($tot > 1 ? "prog" : "check") . ".png");
$shade = ($done ? 255 : 153);
$colour = imagecolorallocate($img, $shade, $shade, $shade);
$font = "res/tahoma.ttf";
imagettftext($img, 12, 0, 47, 27, $colour, $font, $name);
imagettftext($img, 9, 0, 47, 42, $colour, $font, $desc);
if ($tot > 1) {
    imagettftext($img, 8, 0, 11, 59, $colour, $font, "Progress: " . $cur . " / " . $tot);
    $width = (776 * ($cur / $tot));
    imagefilledrectangle($img, 12, 64, $width + 12, 78, $colour);
}
header("Content-Type: image/png");
imagepng($img);
?>
