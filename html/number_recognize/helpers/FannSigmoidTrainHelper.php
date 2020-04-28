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

class FannSigmoidTrainHelper
{
    private const DATA_LOCATION = "data/fann";

    public function train(string $imagePath, string $labelsPath)
    {
        $dataFile = self::DATA_LOCATION . "/train_sigmoid.fann";

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

        // Extract labels array:
        $labels = HelperFunctions::readLabels($labelsPath);
        $labelsCount = count($labels);
        HelperFunctions::printInfo("Read train labels.");

        // Extract train images array:
        $images = HelperFunctions::readImagesDataAsFloatBetween0And1($imagePath);
        $imagesCount = count($images);
        HelperFunctions::printInfo("Read train images.");

        // Create data file:
        if (!is_file($dataFile)) {
            file_put_contents($dataFile, $imagesCount . " 784 10");
        } else {
            HelperFunctions::printError("File $dataFile exist.");
            return;
        }

        $fp = fopen($dataFile, 'a');
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

            /** @var float[] $trainingItem */
            fwrite($fp, implode(" ", $trainingItem)); // Inputs.
            // Output.
            //-1 -1 <- the 2 inputs for the 1st group
            //-1    <- the 1 output for the 1st group

            $i++;
        }
        fclose($fp);
        HelperFunctions::printInfo("Prepared training DataSet.");

        // Training network:
        //$this->trainNetwork($labels, $images);

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Data location: " . self::DATA_LOCATION);
        HelperFunctions::printInfo("Used for train {$imagesCount} images and {$labelsCount} labels.");
        // HelperFunctions::printInfo("Train global error is {$globalError}.");
    }

    private function trainNetwork(array $labels, array $images)
    {
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
        $fann = fann_create_standard(3, 784, 15, 10);
        if (!$fann) {
            quit("ERROR: Error to get NN instance");
        }

        // Sets the activation function for all of the hidden layers:
        fann_set_activation_function_hidden($fann, FANN_SIGMOID);

        // Sets the activation function for the output layer:
        fann_set_activation_function_output($fann, FANN_SIGMOID);

//        // Sets the callback function for use during training.
//        // It means that it is called from fann_train_on_data() or fann_train_on_file().
//        fann_set_callback($fann,
//            function ($nn, $train, $max_epochs, $epochs_between_reports, $desired_error, $epoch) use ($testData) {
//                println('Epoch: ' . $epoch);
//                println('Loss: ' . fann_test_data($nn, $testData));
//                return true;
//            });

        /*
         * Trains on an entire dataset, which is read from file, for a period of time.
         *
         * Parameters:
         * ===========
         * 1 - FANN object.
         * 2 - $filename
         * 3 - The maximum number of epochs the training should continue (experimental way to decide).
         * 4 - The number of epochs between calling a user function. A value of zero means that user function is not called.
         * 5 - It mean 1 error in 1000 samples, like 1 mistake in 1000 tries.
         */
        if (fann_train_on_file($fann, $filename, 150, 1, 0.001)) {
            // Saves the entire network to a configuration file:
            fann_save($fann, self::DATA_LOCATION . "/mnist_sigmoid.net");
        }

        // Destroys the entire network and properly freeing all the associated memory:
        fann_destroy($fann);
    }
}
