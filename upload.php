<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	define("WIDTH_I", "240");
	define("HEIGHT_I", "240");

	$fileName = $_FILES["pic"]["name"];
	$fileTmpLoc = $_FILES["pic"]["tmp_name"];

	$moveResult = move_uploaded_file($fileTmpLoc, "$fileName");

	if ($moveResult == true) {
		make_thumb($fileName, $fileName, WIDTH_I, HEIGHT_I);
		echo "<div id='data'>" . data_uri($fileName) . "</div>";
		@unlink($fileName);
	}
}
function get_extension($str) {
	$str = strtolower($str);
	$arr = explode('.', $str);
	if (sizeof($arr) < 2) {
		return "";
	}
	return $str = $arr[sizeof($arr) - 1];
}
function data_uri($file) {
	$type = get_extension($file);
	$mime = 'image/' . $type;
	$contents = file_get_contents($file);
	$base64 = base64_encode($contents);
	return ('data:' . $mime . ';base64,' . $base64);
}
function make_thumb($img_name, $filename, $new_w, $new_h) {

	$ext = get_extension($img_name);

	if (!strcmp("jpg", $ext) || !strcmp("jpeg", $ext)) $src_img = imagecreatefromjpeg($img_name);

	if (!strcmp("png", $ext)) $src_img = imagecreatefrompng($img_name);

	if (!strcmp("gif", $ext)) $src_img = imagecreatefrompng($img_name);

	$old_x = imageSX($src_img);
	$old_y = imageSY($src_img);

	$ratio1 = $old_x / $new_w;
	$ratio2 = $old_y / $new_h;
	if ($ratio1 > $ratio2) {
		$thumb_w = $new_w;
		$thumb_h = $old_y / $ratio1;
	} else {
		$thumb_h = $new_h;
		$thumb_w = $old_x / $ratio2;
	}

	$dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);
	imagealphablending($dst_img, false);
	imagesavealpha($dst_img, true);
	imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);

	if (!strcmp("png", $ext)) imagepng($dst_img, $filename);
	if (!strcmp("jpg", $ext) || !strcmp("jpeg", $ext)) imagejpeg($dst_img, $filename, 100);
	if (!strcmp("gif", $ext)) imagegif($dst_img, $filename);

	imagedestroy($dst_img);
	imagedestroy($src_img);
}
