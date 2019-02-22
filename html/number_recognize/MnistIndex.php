<?php

declare(strict_types=1);

$response = null;

$data = json_decode($_POST['image']);

//print_r($data);
//print_r(count($data));
//echo json_encode($response);

// Make image:
$im = imagecreatetruecolor(28, 28);

// Define som colors:
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);

// Draw image by array:
$x = 0;
$y = 0;
foreach ($data as $item) {
    if ($item) {
        imagesetpixel($im, $x, $y, $black);
    } else {
        imagesetpixel($im, $x, $y, $white);
    }

//    $x++;
//    if ($x >= 28) {
//        $x = 0;
//        $y++;
//    }
    $y++;
    if ($y >= 28) {
        $y = 0;
        $x++;
    }
}

// Save image:
try {
    imagepng($im, "data/web_draw_png/web_draw_" . date_format(new \DateTime(), 'Y-m-d_H-i-s') . ".png");
} catch (Exception $e) {
    echo "{$e->getCode()}: {$e->getMessage()}";
}
imagedestroy($im);
