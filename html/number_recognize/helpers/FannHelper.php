<?php
/*
* @copyright C UAB NFQ Technologies
*
* This Software is the property of NFQ Technologies
* and is protected by copyright law – it is NOT Freeware.
*
* Any unauthorized use of this software without a valid license key
* is a violation of the license agreement and will be prosecuted by
* civil and criminal law.
*
* Contact UAB NFQ Technologies:
* E-mail: info@nfq.lt
* http://www.nfq.lt
*
*/

declare(strict_types=1);

namespace number_recognize\helpers;

class FannHelper
{
    public const DATA_LOCATION = "data/fann";
    public const TRAINING_DATA_FILE = self::DATA_LOCATION . "/train_data.fann";
    public const NETWORK_CONFIGURATION_FILE = self::DATA_LOCATION . "/fann_mnist_sigmoid.net";
    public const INPUT_NEURONS_AMOUNT = 784;
    public const OUTPUT_NEURONS_AMOUNT = 10;

    public function generateTrainingDataFile(string $imagePath, string $labelsPath)
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin FANN training data file generation.");

        // Do some checks:
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Images file {$imagePath} not exist.");
            return;
        }
        if (!file_exists($imagePath)) {
            HelperFunctions::printError("Labels file {$labelsPath} not exist.");
            return;
        }

        // Extract labels array:
        $labels = HelperFunctions::readLabels($labelsPath);
        $labelsCount = count($labels);
        HelperFunctions::printInfo("Read train labels.");

        // Extract train images array:
        $images = HelperFunctions::readImagesDataAsFloatBetween0And1($imagePath);
        $imagesCount = count($images);
        HelperFunctions::printInfo("Read train images.");

        // Create data file:
        if (!is_file(self::TRAINING_DATA_FILE)) {
            file_put_contents(self::TRAINING_DATA_FILE,
                $imagesCount . " " . self::INPUT_NEURONS_AMOUNT . " " . self::OUTPUT_NEURONS_AMOUNT);
        } else {
            HelperFunctions::printError("File " . self::TRAINING_DATA_FILE . " exist.");
            return;
        }

        $fp = fopen(self::TRAINING_DATA_FILE, 'a');
        $i = 0;
        foreach ($images as $trainingItem) {
            $answers = [];
            for ($j = 0; $j <= 9; ++$j) {
                if ($j === (int)$labels[$i]) {
                    array_push($answers, 1);
                } else {
                    array_push($answers, 0);
                }
            }

            /** @var float[] $trainingItem */
            fwrite($fp, PHP_EOL . implode(" ", $trainingItem)); // Inputs.
            fwrite($fp, PHP_EOL . implode(" ", $answers)); // Outputs.

            $i++;
        }
        fclose($fp);
        HelperFunctions::printInfo("Prepared training DataSet.");

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Training data location: " . self::TRAINING_DATA_FILE);
        HelperFunctions::printInfo("Used for train {$imagesCount} images and {$labelsCount} labels.");
    }
}
