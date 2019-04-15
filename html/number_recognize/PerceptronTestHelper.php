<?php

declare(strict_types=1);

namespace number_recognize;

use DateTime;
use Exception;

class PerceptronTestHelper
{
    private const DATA_LOCATION = "data/train_perceptron";

    public function test(string $imagePath, string $labelsPath): void
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        echo date_format(new DateTime(), 'Y.m.d H:i:s') . ' INFO: Begin testing with perceptron.' . PHP_EOL;

        // Do some checks:
        if (!file_exists($imagePath)) {
            echo date_format(new DateTime(),
                    'Y.m.d H:i:s') . " ERROR: Images file {$imagePath} not exist." . PHP_EOL;
            return;
        }
        if (!file_exists($imagePath)) {
            echo date_format(new DateTime(),
                    'Y.m.d H:i:s') . " ERROR: Labels file {$labelsPath} not exist." . PHP_EOL;
            return;
        }

        // Extract network data:
        /** @var Perceptron[] $perceptrons */
        $perceptrons = [];
        for ($i = 0; $i <= 9; ++$i) {
            $perceptrons[] = $this->getPerceptron($i);
        }

        // Extract labels array:
        $labelsArray = HelperFunctions::readLabels($labelsPath);

        // Open images path:
        $streamImages = fopen($imagePath, 'rb');

        $testDataCount = 0;
        $guessingSuccessCount = 0;
        $guessingMoreAsOneCount = 0;
        try {
            // Binary-safe file read up to 16 bytes from the file pointer $stream:
            $header = fread($streamImages, 16);

            // Unpack data from binary string into an array according to the given format (first parameter):
            $fields = unpack('Nmagic/Nsize/Nrows/Ncols', $header);

            // Check if magic image is ok as expected:
            if ($fields['magic'] !== MnistDataSetReader::MAGIC_IMAGE) {
                throw new Exception('Invalid magic number: ' . $imagePath);
            }

            // Looping all in file available images:
            for ($i = 0; $i < $fields['size']; $i++) {
                $testDataCount++;

                // Read image:
                $imageBytes = fread($streamImages, $fields['rows'] * $fields['cols']);
                $imageBytesBlackWhite = [];

                // Converting to byte array:
                $imageBytesArray = unpack('C*', $imageBytes);

                // Draw number:
                foreach ($imageBytesArray as $imageByte) {
                    // Make image black/white if needed:
                    $imageBytesBlackWhite[] = $imageByte > 0 ? 1 : 0;
                }

                // Testing current number:
                $foundNumber = 0;
                for ($number = 0; $number <= 9; ++$number) {
                    if ($perceptrons[$number]->test($imageBytesBlackWhite)) {
                        if ($labelsArray[$i] === $number) {
                            $foundNumber++;
                        }
                    }
                }
                if ($foundNumber === 1) {
                    $guessingSuccessCount++;
                }
                if ($foundNumber > 1) {
                    $guessingMoreAsOneCount++;
                }
            }


        } finally {
            fclose($streamImages);
        }

        // Information about results:
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)) . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: peak of memory allocated by PHP: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)) . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Done testing in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds) . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Data location: " . self::DATA_LOCATION . PHP_EOL;
        echo date_format(new DateTime(), 'Y.m.d H:i:s') . " INFO: Test numbers amount: {$testDataCount}" . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Success guessing amount: {$guessingSuccessCount}" . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: More as 1 number amount: {$guessingMoreAsOneCount}" . PHP_EOL;
    }

    private function getPerceptron(int $number): Perceptron
    {
        $data = file_get_contents(self::DATA_LOCATION . "/perceptron_for_number_{$number}.dat");

        $perceptron = unserialize($data);

        return $perceptron;
    }
}
