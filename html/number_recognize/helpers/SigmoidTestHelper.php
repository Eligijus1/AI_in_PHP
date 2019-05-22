<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use number_recognize\neuralnetwork\Sigmoid;

class SigmoidTestHelper
{
    private const DATA_LOCATION = "data/train_sigmoid";

    public function test(string $imagePath, string $labelsPath): void
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin testing with sigmoid.");

        // Do some checks:
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Images file {$imagePath} not exist.");
            return;
        }
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Labels file {$labelsPath} not exist.");
            return;
        }

        // Extract from file Sigmoid network:
        $sigmoid = $this->getSigmoid();

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
            $output = $sigmoid->activate($image);
            $guessValue = $this->guessValueFromOutput($output);

            // Register success guess:
            if ($guessValue === (int)$labels[$key]) {
                $successGuessAmount++;
            }
        }
        HelperFunctions::printInfo("Prepared testing DataSet.");

        // Reset not required variables:
        $images = null;
        $labels = null;

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Data location: " . self::DATA_LOCATION);
        HelperFunctions::printInfo("Used for train {$imagesCount} images and {$labelsCount} labels.");
        HelperFunctions::printInfo("Success guess amount: {$successGuessAmount}.");
    }

    private function getSigmoid(): Sigmoid
    {
        $data = file_get_contents(self::DATA_LOCATION . "/sigmoid.dat");

        $sigmoid = unserialize($data);

        return $sigmoid;
    }

    private function guessValueFromOutput(array $output): int
    {
        $value = -1;
        $guessAmount = 0;

        foreach ($output as $key => $networkOutput) {
            if ($networkOutput >= 0.9 && $networkOutput <= 1) {
                $guessAmount++;
                $value = $key;
            }
        }

        if ($guessAmount > 1) {
            $value = -1;
        }

        return $value;
    }
}