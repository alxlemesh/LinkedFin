<?php
/**
 * Serves default placeholder images (avatar or banner) generated with PHP GD.
 * Usage: /img/defaults.php?type=avatar   or   /img/defaults.php?type=banner
 */

$type = $_GET['type'] ?? 'avatar';

if ($type === 'banner') {
    $width  = 1584;
    $height = 396;
    $bg     = [58, 88, 130];   // deep LinkedIn-blue
    $text   = '';
} else {
    $width  = 400;
    $height = 400;
    $bg     = [14, 116, 144];  // LinkedFin teal
    $text   = 'AJ';
}

header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');

$img = imagecreatetruecolor($width, $height);
$bgColor = imagecolorallocate($img, $bg[0], $bg[1], $bg[2]);
imagefill($img, 0, 0, $bgColor);

if ($text !== '') {
    $white = imagecolorallocate($img, 255, 255, 255);
    $font  = 5; // built-in font
    $tw    = imagefontwidth($font)  * strlen($text);
    $th    = imagefontheight($font);
    $scale = 8;
    // Draw big text by tiling
    $x = (int)(($width  - $tw  * $scale) / 2);
    $y = (int)(($height - $th  * $scale) / 2);
    // Use imagestring for simple centered initials
    imagestring($img, $font, (int)(($width - $tw) / 2), (int)(($height - $th) / 2), $text, $white);
}

imagepng($img);
imagedestroy($img);
