<?php

declare(strict_types=1);

namespace number_recognize\helpers;

class SoftmaxTrainHelper
{
    private const DATA_LOCATION = "data/train_softmax";

    public function train(string $imagePath, string $labelsPath): void
    {
        $dataFile = self::DATA_LOCATION . "/softmax.dat";
        //$dataFileBackup = self::DATA_LOCATION . "/softmax_15_hidden_layers_{$learningRate}_learning_rate_{$momentum}_momentum_{$minimumError}_min_error_{$maxNumEpochs}_epochs.dat";

        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin training with sigmoid.");
        //HelperFunctions::printInfo("Learning rate: {$learningRate}; Momentum: {$momentum}; Max epochs: {$maxNumEpochs}");

        // Do some checks:
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Images file {$imagePath} not exist.");
            return;
        }
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Labels file {$labelsPath} not exist.");
            return;
        }

        // TODO

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Data location: " . self::DATA_LOCATION);
//        HelperFunctions::printInfo("Used for train {$imagesCount} images and {$labelsCount} labels.");
//        HelperFunctions::printInfo("Train global error is {$globalError}.");
    }
}
