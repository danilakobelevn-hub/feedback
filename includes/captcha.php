<?php
session_start();

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
$_SESSION['captcha'] = $code;

$image = imagecreatetruecolor(200, 50);
$background = imagecolorallocate($image, 255, 255, 255);
$textcolor = imagecolorallocate($image, 0, 0, 0);

imagefilledrectangle($image, 0, 0, 200, 50, $background);

for ($i = 0; $i < 1000; $i++) {
    $pixel = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imagesetpixel($image, rand(0, 200), rand(0, 50), $pixel);
}

for ($i = 0; $i < 5; $i++) {
    $linecolor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imageline($image, rand(0, 200), rand(0, 50), rand(0, 200), rand(0, 50), $linecolor);
}

$font = __DIR__ . '/fonts/arial.ttf';
if (file_exists($font)) {
    for ($i = 0; $i < 6; $i++) {
        $char = $code[$i];
        $angle = rand(-10, 10);
        $x = 30 + $i * 25;
        $y = 35 + rand(-5, 5);
        $color = imagecolorallocate($image, rand(0, 100), rand(0, 100), rand(0, 100));
        imagettftext($image, 20, $angle, $x, $y, $color, $font, $char);
    }
} else {
    imagestring($image, 5, 50, 18, $code, $textcolor);
}

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>