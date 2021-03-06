<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use Exception;
use number_recognize\neuralnetwork\Softmax;

class SoftmaxTestHelper
{
    private const DATA_LOCATION = "data/train_softmax";

    /**
     * @param string $imagePath
     * @param string $labelsPath
     * @param string $networkFile
     *
     * @return int
     *
     * @throws Exception
     */
    public function test(string $imagePath, string $labelsPath, ?string $networkFile = null): int
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin testing with softmax.");

        // Do some checks:
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Images file {$imagePath} not exist.");
            return 0;
        }
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Labels file {$labelsPath} not exist.");
            return 0;
        }

        // Extract from file Softmax network:
        $softmax = $this->getSoftmax($networkFile);

        // Extract test images array:
        $images = HelperFunctions::readImagesDataAsFloatBetween0And1($imagePath);
        $imagesCount = count($images);
        HelperFunctions::printInfo("Read test images.");

        // Extract test labels array:
        $labels = HelperFunctions::readLabels($labelsPath);
        $labelsCount = count($labels);
        HelperFunctions::printInfo("Read test labels.");

        // Prepare testing DataSet:
        $successGuessAmount = 0;
        foreach ($images as $key => $image) {
            // Testing current item:
            $output = $softmax->hypothesis($image);
            $guessValue = $this->guessValueFromOutput($output);

            // Register success guess:
            if ($guessValue === (int)$labels[$key]) {
                $successGuessAmount++;
            }
        }

        // Reset not required variables:
        $images = null;
        $labels = null;

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done testing in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Data location: " . self::DATA_LOCATION);
        HelperFunctions::printInfo("Used for testing {$imagesCount} images and {$labelsCount} labels.");
        HelperFunctions::printInfo("Learning rate: {$softmax->getLearningRate()}.");
        HelperFunctions::printInfo("Epochs number: {$softmax->getEpochsNumber()}.");
        HelperFunctions::printInfo("Success guess amount: {$successGuessAmount}.");
        //HelperFunctions::printInfo("Momentum: {$sigmoid->getMomentum()}.");
        //HelperFunctions::printInfo("Global error: {$sigmoid->getLatestTrainGlobalError()}.");

        return $successGuessAmount;
    }

    public function getSoftmax(?string $networkFile = null): Softmax
    {
        if (!$networkFile) {
            $networkFile = self::DATA_LOCATION . "/softmax.dat";
        }

        if (!file_exists($networkFile)) {
            HelperFunctions::printError("File {$networkFile} not exist.");
            exit(1);
        }

        $data = file_get_contents($networkFile);

        $sigmoid = unserialize($data);

        return $sigmoid;
    }

    private function guessValueFromOutput(array $output): int
    {
        $value = -1;
        $guessPercentLevel = 0;

        foreach ($output as $key => $networkOutput) {
            if ($networkOutput > $guessPercentLevel && $networkOutput <= 1) {
                $value = $key;
                $guessPercentLevel = $networkOutput;
            }
        }

        return $value;
    }
}
