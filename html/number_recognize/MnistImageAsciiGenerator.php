<?php

declare(strict_types=1);

use number_recognize\HelperFunctions;

class MnistImageAsciiGenerator
{
    private const DATA_LOCATION = "data/generated_ascii";

    public function generate(string $imagePath, string $labelsPath): void
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        echo date_format(new \DateTime(), 'Y.m.d H:i:s') . ' INFO: Begin training with perceptron.' . PHP_EOL;

        // Do some checks:
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

        // Call method, responsible to train:
        $i = $this->generateAscii($imagePath, $labelsPath);

        // Information about results:
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Checked {$i} numbers." . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)) . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: peak of memory allocated by PHP: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)) . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds) . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Data location: " . self::DATA_LOCATION . PHP_EOL;
    }

    private function generateAscii(string $imagePath, string $labelsPath): int
    {
        // Create work if not exist:
        if (!file_exists(self::DATA_LOCATION)) {
            mkdir(self::DATA_LOCATION, 0777, true);
        }

        // Extract labels array:
        $labelsArray = HelperFunctions::readLabels($labelsPath);

        // Open images path:
        $streamImages = fopen($imagePath, 'rb');

        $i = 0;
        try {
            // Binary-safe file read up to 16 bytes from the file pointer $stream:
            $header = fread($streamImages, 16);

            // Unpack data from binary string into an array according to the given format (first parameter):
            $fields = unpack('Nmagic/Nsize/Nrows/Ncols', $header);

            // Check if magic image is ok as expected:
            if ($fields['magic'] !== HelperFunctions::MAGIC_IMAGE) {
                throw new \Exception('Invalid magic number: ' . $imagePath);
            }

            // Clean all old files from directory:
            foreach (glob(self::DATA_LOCATION . '/*.*') as $v) {
                unlink($v);
            }

            // Looping all in file available images:
            for ($i = 0; $i < $fields['size']; $i++) {
                // Read image:
                $imageBytes = fread($streamImages, $fields['rows'] * $fields['cols']);

                // Converting to byte array:
                $imageBytesArray = unpack('C*', $imageBytes);

                // Define file name, where will be saved data:
                $fileName = self::DATA_LOCATION . "/" . sprintf("%06d",
                        ($i + 1)) . "_label_" . $labelsArray[$i] . ".txt";

                // Draw number:
                $x = 0;
                $y = 0;
                foreach ($imageBytesArray as $imageByte) {
                    // Make image black/white if needed:
                    $imageByteBlackWhite = $imageByte > 0 ? 1 : 0;

                    file_put_contents($fileName, $imageByteBlackWhite, FILE_APPEND | LOCK_EX);

                    $x++;

                    if ($x >= 28) {
                        $x = 0;
                        $y++;

                        file_put_contents($fileName, PHP_EOL, FILE_APPEND | LOCK_EX);
                    }
                }

                // Interrupting after specified amount of loops:
                if ($i === 5) {
                    break;
                }
            }
        } finally {
            fclose($streamImages);
        }

        return $i;
    }
}
