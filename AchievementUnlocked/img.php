<?
require_once getenv("PHPLIB") . "keystore.php";
mysql_connect(keystore("mysql", "db"), keystore("mysql", "user"), keystore("mysql", "pass"));
mysql_select_db("terrance_labs");
session_start();
if (array_key_exists("tid", $_GET)) {
	$tid = $_GET["tid"];
	$data = mysql_query("SELECT * FROM `ach__tasks` WHERE `tid` = " . $tid . ";");
	$row = mysql_fetch_array($data);
	if (!$row) {
		die("No task found with the ID " . $tid . ".");
	}
	$name = $row["name"];
	$desc = $row["desc"];
	$cur = (int) $row["cur"];
	$tot = (int) $row["tot"];
	if ($tot > 1) {
		$done = ($cur === $tot ? 1 : 0);
	}
	else {
		$done = $cur;
		$cur = 0;
	}
}
else if (array_key_exists("uid", $_GET)) {
	$uid = $_GET["uid"];
	$data = mysql_query("SELECT * FROM `ach__tasks` WHERE `uid` = " . $uid . ";");
	$len = mysql_num_rows($data);
	if (!$len) {
		die("No tasks found for the user with ID " . $uid . ".");
	}
	$rand = rand(0, $len - 1);
	$name = mysql_result($data, $rand, "name");
	$desc = mysql_result($data, $rand, "desc");
	$cur = (int) mysql_result($data, $rand, "cur");
	$tot = (int) mysql_result($data, $rand, "tot");
	if ($tot > 1) {
		$done = ($cur === $tot ? 1 : 0);
	}
	else {
		$done = $cur;
		$cur = 0;
	}
}
else {
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
