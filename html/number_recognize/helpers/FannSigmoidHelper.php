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

namespace number_recognize\helpers;

use DateTime;

class FannSigmoidHelper
{
    public function trainAndTest(
        string $testImagesPath,
        string $testLabelsPath,
        int $maxEpochs,
        int $hiddenLayersCount,
        bool $writeCsvLog,
        string $continueFromFannFile = null
    ) {
        // Declare and set variables:
        $currentEpoch = 0;
        //$epochsBetweenSaves = 100;
        $desiredError = 0.0001;
        $psudoMseResult = $desiredError * 10000;
        $epochsSinceLastSave = 0;
        $bestSuccessGuessAmount = 0;
        $saveFileName = FannHelper::NETWORK_CONFIGURATION_FILE;
        $milliseconds = round(microtime(true) * 1000);
        $logFile = null;
        $fann = null;
        $trainData = fann_read_train_from_file(FannHelper::TRAINING_DATA_FILE); // Read trains data (60 000) from fann file.
        $testImages = HelperFunctions::readImagesDataAsFloatBetween0And1($testImagesPath); // Read test labels (10 000 images)
        $testLabels = HelperFunctions::readLabels($testLabelsPath); // Read test labels

        HelperFunctions::printInfo("Begin FANN sigmoid training.");

        $this->deleteOldFiles();

        // Create log file:
        if ($writeCsvLog) {
            if (!is_file(FannHelper::TRAINING_LOG_FILE)) {
                file_put_contents(FannHelper::TRAINING_LOG_FILE, "DateTime,Epoch,MSE,SuccessGuessAmount");
            } else {
                HelperFunctions::printError("File " . FannHelper::TRAINING_LOG_FILE . " exist.");
                return;
            }
            $logFile = fopen(FannHelper::TRAINING_LOG_FILE, 'a');
        }

        // Create FANN object:
        if ($continueFromFannFile) {
            $fann = fann_create_from_file($continueFromFannFile);
        } else {
            $fann = fann_create_standard(3, FannHelper::INPUT_NEURONS_AMOUNT, $hiddenLayersCount,
                FannHelper::OUTPUT_NEURONS_AMOUNT);
            fann_set_training_algorithm($fann, FANN_TRAIN_BATCH);
            fann_set_activation_function_hidden($fann, FANN_SIGMOID_SYMMETRIC);
            fann_set_activation_function_output($fann, FANN_SIGMOID_SYMMETRIC);
        }

        if ($fann) {
            while (($psudoMseResult > $desiredError) && ($currentEpoch <= $maxEpochs)) {
                $currentEpoch++;
                $epochsSinceLastSave++;

                // Train one epoch:
                $psudoMseResult = fann_train_epoch($fann, $trainData);

                // Extract success amount:
                $successGuessAmount = $this->testNetwork($fann, $testImages, $testLabels);

                // Define file name:
                $saveFileName = str_replace('.net',
                    "_success_{$successGuessAmount}_hidden_layers_{$hiddenLayersCount}_" . date_format(new DateTime(),
                        'Y-m-d_H-i-s') . ".net", FannHelper::NETWORK_CONFIGURATION_FILE);

                // Log data:
                HelperFunctions::printInfo(sprintf("Epoch: %' 6s; MSE: %' -20s; Success guess amount: %' 6s;",
                    $currentEpoch, $psudoMseResult, $successGuessAmount));

                // Write log:
                if ($writeCsvLog) {
                    fwrite($logFile,
                        PHP_EOL . date_format(new DateTime(),
                            'Y-m-d H:i:s') . ",$currentEpoch,$psudoMseResult,$successGuessAmount");
                }

                // Backup network conditions:
                //if (($epochsSinceLastSave >= $epochsBetweenSaves) && ($successGuessAmount > $bestSuccessGuessAmount)) {
                if ($successGuessAmount > 9000 && $successGuessAmount > $bestSuccessGuessAmount) {
                    $bestSuccessGuessAmount = $successGuessAmount;

                    // Save a Snapshot of the ANN:
                    fann_save($fann, $saveFileName);
                    $epochsSinceLastSave = 0;
                }
            }

            // Save the final network
            fann_save($fann, $saveFileName);
            fann_destroy($fann);
        } else {
            quit("ERROR: Error to get NN instance.");
        }

        // Close log file:
        if ($writeCsvLog) {
            fclose($logFile);
        }

        // Information about results:
        HelperFunctions::printInfo('Training Complete!');
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Network configuration file location: " . FannHelper::NETWORK_CONFIGURATION_FILE);
        HelperFunctions::printInfo("Maximum number of epochs the training should continue: " . $maxEpochs);
    }

    private function deleteOldFiles()
    {
        $this->deleteOldFile(FannHelper::NETWORK_CONFIGURATION_FILE);

        $this->deleteOldFile(FannHelper::TRAINING_LOG_FILE);
    }

    private function deleteOldFile(string $file)
    {
        if (is_file($file)) {
            if (!unlink($file)) {
                HelperFunctions::printError("Old file '$file' cannot be deleted due to an error.");
            } else {
                HelperFunctions::printInfo("Old file '$file' has been deleted");
            }
        }
    }

    private function testNetwork($fann, array $testImages, array $testLabels): int
    {
        $successGuessAmount = 0;

        foreach ($testImages as $key => $testImage) {
            $output = fann_run($fann, $testImage);
            $guessValue = $this->guessValueFromOutput($output);

            // Register success guess:
            if ($guessValue === (int)$testLabels[$key]) {
                $successGuessAmount++;
            }
        }

        return $successGuessAmount;
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
