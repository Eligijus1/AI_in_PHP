<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use number_recognize\neuralnetwork\Sigmoid;

class SigmoidTrainHelper
{
    private const DATA_LOCATION = "data/train_sigmoid";

    public function train(string $imagePath, string $labelsPath): void
    {
        $dataFile = self::DATA_LOCATION . "/sigmoid.dat";

        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin training with sigmoid.");

        // Do some checks:
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Images file {$imagePath} not exist.");
            return;
        }
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Labels file {$labelsPath} not exist.");
            return;
        }

        // Training network:
        // NOTE:
        // =====
        // 1 parameter - 784 - input pixels amount; 15 - hidden layer (need adjust); last - output.
        // 2 parameter - learning rate. Need adjust.
        // 3 parameter - momentum. Need adjust.
        // 4 parameter - minimum error.
        // 5 parameter - max num epochs. Need use 2000 or more.
        $sigmoid = new Sigmoid([784, 15, 10], 0.2, 0.7, 0.005, 1);
        HelperFunctions::printInfo("Created Sigmoid object.");

        // Extract train images array:
        $images = HelperFunctions::readImagesData($imagePath);
        $imagesCount = count($images);
        HelperFunctions::printInfo("Read train images.");

        // Extract labels array:
        $labels = HelperFunctions::readLabels($labelsPath);
        $labelsCount = count($labels);
        HelperFunctions::printInfo("Read train labels.");

        // Prepare training DataSet:
        $trainingDataSet = [];
        $i = 0;
        foreach ($images as $image) {
            // Converting image bytes to float between 0 and 1:
            $trainingItem = array_map(function ($b) {
                return $b / 255;
            }, array_values($image));

            // Training data set should have answer, that contain answer data:
            for ($j = 0; $j <= 9; ++$j) {
                if ($j === (int)$labels[$i]) {
                    array_push($trainingItem, 1);
                } else {
                    array_push($trainingItem, 0);
                }
            }

            $trainingDataSet[$i] = $trainingItem;

            // Update loop:
            $i++;
        }
        HelperFunctions::printInfo("Prepared training DataSet.");

        // Reset not required variables:
        $images = null;
        $labels = null;

        // Call train methods, responsible for training:
        // WARNING: this operation consuming a lot resources.
        $trainStatus = $sigmoid->train($trainingDataSet) ? 'OK' : 'Failed';
        HelperFunctions::printInfo("Training finished.");

        // Create work if not exist:
        if (!file_exists(self::DATA_LOCATION)) {
            mkdir(self::DATA_LOCATION, 0777, true);
        }

        // Save object to disc:
        $s = serialize($sigmoid);
        file_put_contents($dataFile, $s);

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Data location: " . self::DATA_LOCATION);
        HelperFunctions::printInfo("Used for train {$imagesCount} images and {$labelsCount} labels. Train result is {$trainStatus}.");
    }
}
