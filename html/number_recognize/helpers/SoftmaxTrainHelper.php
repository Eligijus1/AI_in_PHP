<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use number_recognize\neuralnetwork\Softmax;

class SoftmaxTrainHelper
{
    private const DATA_LOCATION = "data/train_softmax";

    public function train(
        string $imagePath,
        string $labelsPath,
        float $learningRate,
        int $epochs = 1,
        ?string $softmaxNetwork = null
    ): void {
        $dataFile = self::DATA_LOCATION . "/softmax.dat";
        $dataFileBackup = self::DATA_LOCATION . "/softmax_{$learningRate}_learning_rate_{$epochs}_epochs.dat";

        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin training with sigmoid.");
        HelperFunctions::printInfo("Learning rate: {$learningRate}; Epochs: {$epochs}");

        // Do some checks:
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Images file {$imagePath} not exist.");
            return;
        }
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Labels file {$labelsPath} not exist.");
            return;
        }

        // Softmax object:
        $softmax = null;
        if ($softmaxNetwork) {
            $softmax = (new SoftmaxTestHelper())->getSoftmax($softmaxNetwork);
        } else {
            $softmax = new Softmax($learningRate, $epochs);
            HelperFunctions::printInfo("Created Softmax object.");
        }

        // Extract labels array:
        $labels = HelperFunctions::readLabels($labelsPath);
        $labelsCount = count($labels);
        HelperFunctions::printInfo("Read train labels.");

        // Extract train images array:
        $images = HelperFunctions::readImagesDataAsFloatBetween0And1($imagePath);
        $imagesCount = count($images);
        HelperFunctions::printInfo("Read train images.");

        // Begin training with images:
        for ($i = 1; $i <= $epochs; $i++) {
            $loss = $softmax->trainingStep($images, $labels);
            $averageLoss = $loss / count($images);
            $accuracy = $this->calculateAccuracy($softmax, $images, $labels);
            HelperFunctions::printInfo("Step {$i}; Average Loss {$averageLoss}; Accuracy {$accuracy};");
        }

        // Create work if not exist:
        if (!file_exists(self::DATA_LOCATION)) {
            mkdir(self::DATA_LOCATION, 0777, true);
        }

        // Save object to disc:
        $s = serialize($softmax);
        file_put_contents($dataFile, $s);
        file_put_contents($dataFileBackup, $s);

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Data location: " . self::DATA_LOCATION);
        HelperFunctions::printInfo("Used for train {$imagesCount} images and {$labelsCount} labels.");
        HelperFunctions::printInfo("Learning rate is {$learningRate}.");
    }

    /**
     * Accuracy Evaluation
     *
     * @param Softmax $softmax
     * @param array   $images
     * @param array   $labels
     *
     * @return float|int
     */
    private function calculateAccuracy(Softmax $softmax, array $images, array $labels)
    {
        $size = count($images);
        // Loop through all the training examples
        for ($i = 0, $correct = 0; $i < $size; $i++) {
            $image = $images[$i];
            $label = $labels[$i];
            $activations = $softmax->hypothesis($image);
            // Our prediction is index containing the maximum probability
            $prediction = array_search(max($activations), $activations);
            if ($prediction == $label) {
                $correct++;
            }
        }
        // Percentage of correct predictions is the accuracy
        return $correct / $size;
    }
}
