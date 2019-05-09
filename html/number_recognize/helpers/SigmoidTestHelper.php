<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use DateTime;
use number_recognize\Sigmoid;

class SigmoidTestHelper
{
    private const DATA_LOCATION = "data/train_sigmoid";

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

        // Extract from file Sigmoid network:
        $sigmoid = $this->getSigmoid();

        // Extract labels array:
        $labelsArray = HelperFunctions::readLabels($labelsPath);

        // Extract images array:
        $images = HelperFunctions::readImagesData($imagePath);
        $testDataCount = count($images);

        // Looping images:
        foreach ($images as $i => $image) {
            echo "\n{$i}";
            print_r($image);
            return;
            //$sigmoid->
        }
        //$sigmoid->test();

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
    }

    private function getSigmoid(): Sigmoid
    {
        $data = file_get_contents(self::DATA_LOCATION . "/sigmoid.dat");

        $sigmoid = unserialize($data);

        return $sigmoid;
    }
}
