<?php

declare(strict_types=1);

namespace number_recognize;

class MnistImageGenerator
{
    private const IMAGES_LOCATION = "data/generate_specified_number_images";

    public function generateOneNumberImages(string $imagePath, string $labelsPath, int $number): void
    {
        // Do some checks:
        if (($number < 0) || ($number > 9)) {
            echo date_format(new \DateTime(),
                    'Y.m.d H:i:s') . " ERROR: Wrong number {$number}. Value must be between 0 and 9." . PHP_EOL;
            return;
        }
        if (!file_exists($imagePath)) {
            echo date_format(new \DateTime(),
                    'Y.m.d H:i:s') . " ERROR: Images file {$imagePath} not exist." . PHP_EOL;
            return;
        }
        if (!file_exists($imagePath)) {
            echo date_format(new \DateTime(),
                    'Y.m.d H:i:s') . " ERROR: Labels file {$labelsPath} not exist." . PHP_EOL;
            return;
        }

        // Open images path:
        $stream = fopen($imagePath, 'rb');

        try {
            // Binary-safe file read up to 16 bytes from the file pointer $stream:
            $header = fread($stream, 16);

            // Unpack data from binary string into an array according to the given format (first parameter):
            $fields = unpack('Nmagic/Nsize/Nrows/Ncols', $header);

            // Check if magic image is ok as expected:
            if ($fields['magic'] !== MnistDataSetReader::MAGIC_IMAGE) {
                throw new \Exception('Invalid magic number: ' . $imagePath);
            }

            // Create generated files directory if not exist:
            if (!file_exists(self::IMAGES_LOCATION)) {
                mkdir(self::IMAGES_LOCATION, 0777, true);
            }

            // Clean all old files from directory:
            foreach(glob(self::IMAGES_LOCATION.'/*.*') as $v){
                unlink($v);
            }

            // Looping all in file available images:
            for ($i = 0; $i < $fields['size']; $i++) {
                // Read image:
                $imageBytes = fread($stream, $fields['rows'] * $fields['cols']);

                // Converting to byte array:
                $imageBytesArray = unpack('C*', $imageBytes);

                // Make image:
                $im = imagecreatetruecolor(28, 28);

                // Draw number:
                $x = 0;
                $y = 0;
                foreach ($imageBytesArray as $imageByte) {
                    imagesetpixel($im, $x, $y, imagecolorallocate($im, $imageByte, $imageByte, $imageByte));

                    $x++;

                    if ($x >= 28) {
                        $x = 0;
                        $y++;
                    }
                }

                // Invert color
                imagefilter($im, IMG_FILTER_NEGATE);

                // Save image:
                imagepng($im, self::IMAGES_LOCATION . "/" . sprintf("%06d", ($i+1)) . ".png");
                imagedestroy($im);

                // Interrupting after 5 loops:
                if ($i === 5) {
                    return;
                }
            }
        } finally {
            fclose($stream);
        }
    }
}
