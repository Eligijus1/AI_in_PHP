<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use DateTime;
use number_recognize\neuralnetwork\Sigmoid;

class SigmoidTrainHelper
{
    private const DATA_LOCATION = "data/train_sigmoid";

    public function train(string $imagePath, string $labelsPath): void
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin training with perceptron.");

        // Do some checks:
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Images file {$imagePath} not exist.");
            return;
        }
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Labels file {$labelsPath} not exist.");
            return;
        }

        // Extract images array:
        $images = HelperFunctions::readImagesData($imagePath);
        $dataCount = count($images);

        // Training network:
        // NOTE: 1 parameter - input pixels amount; 15 - hidden layer (need adjust); last - output.
        $sigmoid = new Sigmoid([784, 15, 10], 0.2, 0.7, 0.005);
        $trainStatus = $sigmoid->train($images);

        // Create work if not exist:
        if (!file_exists(self::DATA_LOCATION)) {
            mkdir(self::DATA_LOCATION, 0777, true);
        }

        // Save object to disc:
        $s = serialize($sigmoid);
        file_put_contents(self::DATA_LOCATION . "/sigmoid.dat", $s);

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Data location: " . self::DATA_LOCATION);
        HelperFunctions::printInfo("Used for train {$dataCount} data. Train result is {$trainStatus}.");
    }
}
