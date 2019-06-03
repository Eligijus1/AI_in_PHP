<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use number_recognize\neuralnetwork\Sigmoid;

class SigmoidTrainHelper
{
    private const DATA_LOCATION = "data/train_sigmoid";

    public function train(
        string $imagePath,
        string $labelsPath,
        float $learningRate = 0.2,
        float $momentum = 0.7,
        int $maxNumEpochs = 20,
        float $minimumError = 0.005
    ): void {
        $dataFile = self::DATA_LOCATION . "/sigmoid.dat";
        $dataFileBackup = self::DATA_LOCATION . "/sigmoid_15_hidden_layers_{$learningRate}_learning_rate_{$momentum}_momentum_{$minimumError}_min_error_{$maxNumEpochs}_epochs.dat";

        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin training with sigmoid.");
        HelperFunctions::printInfo("Learning rate: {$learningRate}; Momentum: {$momentum}; Max epochs: {$maxNumEpochs}");

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
        $sigmoid = new Sigmoid([784, 15, 10], $learningRate, $momentum, $minimumError, $maxNumEpochs);
        HelperFunctions::printInfo("Created Sigmoid object.");

        // Extract labels array:
        $labels = HelperFunctions::readLabels($labelsPath);
        $labelsCount = count($labels);
        HelperFunctions::printInfo("Read train labels.");

        // Extract train images array:
        $images = HelperFunctions::readImagesDataAsFloatBetween0And1($imagePath);
        $imagesCount = count($images);
        HelperFunctions::printInfo("Read train images.");

        // Prepare training DataSet:
        $trainingDataSet = [];
        $i = 0;
        foreach ($images as $trainingItem) {
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
        $globalError = $sigmoid->train($trainingDataSet);
        HelperFunctions::printInfo("Training finished.");

        // Create work if not exist:
        if (!file_exists(self::DATA_LOCATION)) {
            mkdir(self::DATA_LOCATION, 0777, true);
        }

        // Save object to disc:
        $s = serialize($sigmoid);
        file_put_contents($dataFile, $s);
        file_put_contents($dataFileBackup, $s);

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Data location: " . self::DATA_LOCATION);
        HelperFunctions::printInfo("Used for train {$imagesCount} images and {$labelsCount} labels.");
        HelperFunctions::printInfo("Train global error is {$globalError}.");
    }

    public function getTrainingDataSet(string $imagePath, string $labelsPath): array
    {
        // Extract labels array:
        $labels = HelperFunctions::readLabels($labelsPath);
        HelperFunctions::printInfo("Read train labels.");

        // Extract train images array:
        $images = HelperFunctions::readImagesDataAsFloatBetween0And1($imagePath);
        HelperFunctions::printInfo("Read train images.");

        // Prepare training DataSet:
        $trainingDataSet = [];
        $i = 0;
        foreach ($images as $trainingItem) {
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

        return $trainingDataSet;
    }
}
