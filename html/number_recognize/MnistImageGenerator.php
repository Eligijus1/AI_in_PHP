<?php

declare(strict_types=1);

namespace number_recognize;

class MnistImageGenerator
{
    private const IMAGES_LOCATION = "data/generate_specified_number_images";
    private const MAGIC_IMAGE = 0x00000803;
    private const MAGIC_LABEL = 0x00000801;

    /**
     * @param string $imagePath
     * @param string $labelsPath
     * @param int    $number
     *
     * @param bool   $blackWhite
     *
     * @throws \Exception
     */
    public function generateOneNumberImages(
        string $imagePath,
        string $labelsPath,
        int $number,
        bool $blackWhite = false
    ): void {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . ' INFO: Generator started.' . PHP_EOL;

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

        // Extract labels array:
        $labelsArray = $this->readLabels($labelsPath);

        // Open images path:
        $streamImages = fopen($imagePath, 'rb');

        $i = 0;
        try {
            // Binary-safe file read up to 16 bytes from the file pointer $stream:
            $header = fread($streamImages, 16);

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
            foreach (glob(self::IMAGES_LOCATION . '/*.*') as $v) {
                unlink($v);
            }

            // Looping all in file available images:
            for ($i = 0; $i < $fields['size']; $i++) {
                // Read image:
                $imageBytes = fread($streamImages, $fields['rows'] * $fields['cols']);

                // Converting to byte array:
                $imageBytesArray = unpack('C*', $imageBytes);

                // Make image:
                $im = imagecreatetruecolor(28, 28);

                // Draw number:
                $x = 0;
                $y = 0;
                foreach ($imageBytesArray as $imageByte) {
                    // Make image black/white if needed:
                    if ($blackWhite) {
                        $imageByteBlackWhite = $imageByte > 0 ? 255 : 0;
                        imagesetpixel($im, $x, $y,
                            imagecolorallocate($im, $imageByteBlackWhite, $imageByteBlackWhite, $imageByteBlackWhite));
                    } else {
                        imagesetpixel($im, $x, $y, imagecolorallocate($im, $imageByte, $imageByte, $imageByte));
                    }

                    $x++;

                    if ($x >= 28) {
                        $x = 0;
                        $y++;
                    }
                }

                // Invert color (make number color black and background white):
                imagefilter($im, IMG_FILTER_NEGATE);

                // Save image:
                if ($labelsArray[$i] === $number) {
                    imagepng($im,
                        self::IMAGES_LOCATION . "/" . sprintf("%06d",
                            ($i + 1)) . "_label_" . $labelsArray[$i] . ".png");
                }
                imagedestroy($im);

                // Interrupting after specified amount of loops:
//                if ($i === 100) {
//                    break;
//                }
            }
        } finally {
            fclose($streamImages);
        }

        // Information about results:
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Checked {$i} numbers." . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)) . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: peak of memory allocated by PHP: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)) . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Done generator in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds) . PHP_EOL;
    }

    /**
     * Read MNIST label file.
     *
     * Format: http://yann.lecun.com/exdb/mnist/
     *
     * @param string $labelPath
     *
     * @return array
     * @throws \Exception
     */
    private function readLabels(string $labelPath): array
    {
        $stream = fopen($labelPath, 'rb');
        if (false === $stream) {
            throw new \Exception('Could not open file: ' . $labelPath);
        }
        $labels = [];
        try {
            $header = fread($stream, 8);
            $fields = unpack('Nmagic/Nsize', $header);
            if ($fields['magic'] !== self::MAGIC_LABEL) {
                throw new \Exception('Invalid magic number: ' . $labelPath);
            }
            $labels = fread($stream, $fields['size']);
        } finally {
            fclose($stream);
        }
        return array_values(unpack('C*', $labels));
    }
}
