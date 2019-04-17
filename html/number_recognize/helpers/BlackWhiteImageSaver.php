<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use DateTime;

class BlackWhiteImageSaver
{
    /**
     * @var string $location
     */
    private $location;

    public function __construct(string $location)
    {
        $this->location = $location;

        // Create generated files directory if not exist:
        if (!file_exists($this->location)) {
            mkdir($this->location, 0777, true);
        }

        // Clean all old files from directory:
        foreach (glob($this->location . '/*.png') as $v) {
            unlink($v);
        }
    }

    public function save(array $imageBytesBlackWhite, string $fileName): void
    {
        // Make image:
        $im = imagecreatetruecolor(28, 28);

        // Draw image:
        $x = 0;
        $y = 0;
        foreach ($imageBytesBlackWhite as $imageByte) {
            $imageByteBlackWhite = (int)($imageByte === 1 ? 0 : 255);

            $color = imagecolorallocate($im, $imageByteBlackWhite, $imageByteBlackWhite, $imageByteBlackWhite);

            imagesetpixel($im, $x, $y, $color);

            $x++;

            if ($x >= 28) {
                $x = 0;
                $y++;
            }
        }

        // Save image:
        imagepng($im, $this->location . "/" . $fileName);
        imagedestroy($im);
    }
}
