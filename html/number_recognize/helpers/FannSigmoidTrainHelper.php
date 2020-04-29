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
    public function train(int $maxEpochs)
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin FANN sigmoid training.");

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
        if (fann_train_on_file($fann, FannHelper::TRAINING_DATA_FILE, $maxEpochs, 1, 0.001)) {
            // Saves the entire network to a configuration file:
            fann_save($fann, FannHelper::NETWORK_CONFIGURATION_FILE);
        }

        // Destroys the entire network and properly freeing all the associated memory:
        fann_destroy($fann);

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP:: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Network configuration file location: " . FannHelper::NETWORK_CONFIGURATION_FILE);
        HelperFunctions::printInfo("Maximum number of epochs the training should continue: " . $maxEpochs);
    }
}
