<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use DateTime;
use Exception;
use number_recognize\HelperFunctions;
use number_recognize\MnistDataSetReader;
use number_recognize\Perceptron;

class PerceptronTrainHelper
{
    private const DATA_LOCATION = "data/train_perceptron";

    public function train(string $imagePath, string $labelsPath): void
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        echo date_format(new DateTime(), 'Y.m.d H:i:s') . ' INFO: Begin training with perceptron.' . PHP_EOL;

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

        // Call method, responsible to train:
        $this->trainPerceptron($imagePath, $labelsPath, 0);
        $this->trainPerceptron($imagePath, $labelsPath, 1);
        $this->trainPerceptron($imagePath, $labelsPath, 2);
        $this->trainPerceptron($imagePath, $labelsPath, 3);
        $this->trainPerceptron($imagePath, $labelsPath, 4);
        $this->trainPerceptron($imagePath, $labelsPath, 5);
        $this->trainPerceptron($imagePath, $labelsPath, 6);
        $this->trainPerceptron($imagePath, $labelsPath, 7);
        $this->trainPerceptron($imagePath, $labelsPath, 8);
        $this->trainPerceptron($imagePath, $labelsPath, 9);

        // Information about results:
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)) . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: peak of memory allocated by PHP: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)) . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds) . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Data location: " . self::DATA_LOCATION . PHP_EOL;
    }

    private function trainPerceptron(string $imagePath, string $labelsPath, int $number): void
    {
        $dataFile = self::DATA_LOCATION . "/perceptron_for_number_{$number}.dat";

        // Create work if not exist:
        if (!file_exists(self::DATA_LOCATION)) {
            mkdir(self::DATA_LOCATION, 0777, true);
        }

        // Extract labels array:
        $labelsArray = HelperFunctions::readLabels($labelsPath);

        // Open images path:
        $streamImages = fopen($imagePath, 'rb');

        // Prepare perceptron object:
        $perceptron = new Perceptron(28 * 28);

        $i = 0;
        $j = 0;
        try {
            // Binary-safe file read up to 16 bytes from the file pointer $stream:
            $header = fread($streamImages, 16);

            // Unpack data from binary string into an array according to the given format (first parameter):
            $fields = unpack('Nmagic/Nsize/Nrows/Ncols', $header);

            // Check if magic image is ok as expected:
            if ($fields['magic'] !== MnistDataSetReader::MAGIC_IMAGE) {
                throw new Exception('Invalid magic number: ' . $imagePath);
            }

            // Delete old file if exist:
            if (file_exists($dataFile)) {
                unlink($dataFile);
            }

            // Looping all in file available images:
            for ($i = 0; $i < $fields['size']; $i++) {
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

                // Save image:
                if ($labelsArray[$i] === $number) {
                    $perceptron->train($imageBytesBlackWhite, true);
                    $j++;
                } else {
                    $perceptron->train($imageBytesBlackWhite, false);
                }
            }
        } finally {
            fclose($streamImages);
        }

        // Save object to disc:
        $s = serialize($perceptron);
        file_put_contents($dataFile, $s);

        // Output debug information:
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Bias for {$number}: " . $perceptron->getBias() . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Total handled {$i} numbers. {$j} numbers was '{$number}'." . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Data saved to file '{$dataFile}'." . PHP_EOL;
    }
}

