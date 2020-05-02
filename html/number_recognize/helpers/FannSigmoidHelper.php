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
    public function trainAndTest(string $testImagesPath, string $testLabelsPath, int $maxEpochs)
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin FANN sigmoid training.");

        $this->deleteOldFiles();

        // Create log file:
        if (!is_file(FannHelper::TRAINING_LOG_FILE)) {
            file_put_contents(FannHelper::TRAINING_LOG_FILE, "DateTime,Epoch,MSE,SuccessGuessAmount");
        } else {
            HelperFunctions::printError("File " . FannHelper::TRAINING_LOG_FILE . " exist.");
            return;
        }
        $logFile = fopen(FannHelper::TRAINING_LOG_FILE, 'a');

        // Extract test images array:
        $testImages = HelperFunctions::readImagesDataAsFloatBetween0And1($testImagesPath);
        HelperFunctions::printInfo("Read test images.");

        // Extract test labels array:
        $testLabels = HelperFunctions::readLabels($testLabelsPath);
        HelperFunctions::printInfo("Read test labels.");

        /* Creates a standard fully connected back propagation neural network
         * There will be a bias neuron in each layer (except the output layer),
         * and this bias neuron will be connected to all neurons in the next layer.
         * When running the network, the bias nodes always emits 1.
         *
         * Parameters:
         * ===========
         * 1 - The total number of layers including the input and the output layer.
         * 2 - Number of neurons in the first (input) layer.
         * 3 - Number of neurons in the second (hidden) layer (experimental way to decide).
         * 4 - Number of neurons in the 3rd (Third) layer - output. (0 - 9)
         */
        $fann = fann_create_standard(3, FannHelper::INPUT_NEURONS_AMOUNT, 15, FannHelper::OUTPUT_NEURONS_AMOUNT);
        if (!$fann) {
            quit("ERROR: Error to get NN instance.");
        }

        if ($fann) {
            HelperFunctions::printInfo("Training ANN...");

            // Configure the ANN:

            // Standard backpropagation algorithm, where the weights are updated after calculating the
            // mean square error for the whole training set. This means that the weights are only updated
            // once during a epoch. For this reason some problems, will train slower with this algorithm.
            // But since the mean square error is calculated more correctly than in incremental training,
            // some problems will reach a better solutions with this algorithm.
            fann_set_training_algorithm($fann, FANN_TRAIN_BATCH);
            fann_set_activation_function_hidden($fann, FANN_SIGMOID_SYMMETRIC);
            fann_set_activation_function_output($fann, FANN_SIGMOID_SYMMETRIC);

            // Read training data:
            $trainData = fann_read_train_from_file(FannHelper::TRAINING_DATA_FILE);

            // Check if psudo_mse_result is greater than our desired_error
            // if so keep training so long as we are also under max_epochs:
            $currentEpoch = 0;
            $epochsBetweenSaves = 100; // Minimum number of epochs between saves
            $desiredError = 0.0001;
            $psudoMseResult = $desiredError * 10000; // 1 - Initialize psudo mse (mean squared error) to a number greater than the desired_error this is what the network is trying to minimize.
            $epochsSinceLastSave = 0;
            //$bestMse = $psudoMseResult; // keep the last best seen MSE network score here.
            $bestSuccessGuessAmount = 0;
            while (($psudoMseResult > $desiredError) && ($currentEpoch <= $maxEpochs)) {
                $currentEpoch++;
                $epochsSinceLastSave++;

                // See: http://php.net/manual/en/function.fann-train-epoch.php
                // Train one epoch with the training data stored in data.
                //
                // One epoch is where all of the training data is considered
                // exactly once.
                //
                // This function returns the MSE error as it is calculated
                // either before or during the actual training. This is not the
                // actual MSE after the training epoch, but since calculating this
                // will require to go through the entire training set once more.
                // It is more than adequate to use this value during training.
                $psudoMseResult = fann_train_epoch($fann, $trainData);

                // Extract success amount:
                $successGuessAmount = $this->testNetwork($fann, $testImages, $testLabels);

                // Log data:
                HelperFunctions::printInfo(sprintf("Epoch: %' 6s; MSE: %' -20s; Success guess amount: %' 6s;",
                    $currentEpoch, $psudoMseResult, $successGuessAmount));
                fwrite($logFile,
                    PHP_EOL . date_format(new DateTime(),
                        'Y-m-d H:i:s') . ",$currentEpoch,$psudoMseResult,$successGuessAmount");

                // If we haven't saved the ANN in a while...
                // and the current network is better then the previous best network
                // as defined by the current MSE being less than the last best MSE
                // Save it!
                if (($epochsSinceLastSave >= $epochsBetweenSaves) && ($successGuessAmount > $bestSuccessGuessAmount)) {
                    //$bestMse = $psudoMseResult; // we have a new best mse (mean square error)
                    $bestSuccessGuessAmount = $successGuessAmount;

                    // Save a Snapshot of the ANN:
                    // $temSaveFile = FannHelper::NETWORK_CONFIGURATION_FILE . date_format(new DateTime(), "_Y.m.d_H-i-s_SuccessGuessAmount_{$successGuessAmount}_Epoch_{$currentEpoch}");
                    $temSaveFile = FannHelper::NETWORK_CONFIGURATION_FILE . "_success_{$successGuessAmount}_epoch_{$currentEpoch}";
                    fann_save($fann, $temSaveFile);
                    $epochsSinceLastSave = 0; // reset the count
                }
            } // While we"re training

            HelperFunctions::printInfo('Training Complete! Saving Final Network.');

            // Save the final network
            fann_save($fann, FannHelper::NETWORK_CONFIGURATION_FILE);
            fann_destroy($fann); // free memory
        }

        // Close log file:
        fclose($logFile);

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Network configuration file location: " . FannHelper::NETWORK_CONFIGURATION_FILE);
        HelperFunctions::printInfo("Maximum number of epochs the training should continue: " . $maxEpochs);
        //(new FannSigmoidTestHelper())->test(testImagePath, testLabelPath);
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
