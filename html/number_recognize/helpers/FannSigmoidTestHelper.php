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

class FannSigmoidTestHelper
{
    public function test(string $imagePath, string $labelsPath)
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        HelperFunctions::printInfo("Begin FANN sigmoid testing.");

        // Extract test images array:
        $images = HelperFunctions::readImagesDataAsFloatBetween0And1($imagePath);
        $imagesCount = count($images);
        HelperFunctions::printInfo("Read test images.");

        // Extract test labels array:
        $labels = HelperFunctions::readLabels($labelsPath);
        $labelsCount = count($labels);
        HelperFunctions::printInfo("Read test labels.");

        // Interrupt application if train file not exist:
        if (!is_file(FannHelper::NETWORK_CONFIGURATION_FILE)) {
            die("The file xor_float.net has not been created! Please run simple_train.php to generate it");
        }

        // Constructs a backpropagation neural network from a configuration file:
        $ann = fann_create_from_file(FannHelper::NETWORK_CONFIGURATION_FILE);

        // Check if neural network created:
        if (!$ann) {
            die("ANN could not be created");
        }

        // Testing values:
        $successGuessAmount = 0;
        foreach ($images as $key => $image) {
            $output = fann_run($ann, $image);
            $guessValue = $this->guessValueFromOutput($output);

            // Register success guess:
            if ($guessValue === (int)$labels[$key]) {
                $successGuessAmount++;
            }
        }
        HelperFunctions::printInfo("Finished testing.");

        // Information about results:
        HelperFunctions::printInfo("Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)));
        HelperFunctions::printInfo("Peak of memory allocated by PHP: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)));
        HelperFunctions::printInfo("Done testing in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds));
        HelperFunctions::printInfo("Data location: " . FannHelper::DATA_LOCATION);
        HelperFunctions::printInfo("Used for testing {$imagesCount} images and {$labelsCount} labels.");
        HelperFunctions::printInfo("Learning rate: " . fann_get_learning_rate($ann));
        HelperFunctions::printInfo("Learning momentum: " . fann_get_learning_momentum($ann));
        HelperFunctions::printInfo("Number of cascade candidate stagnation epochs: " . fann_get_cascade_candidate_stagnation_epochs($ann)); // The number of epochs training is allowed to continue without changing the MSE (mean square error) by a fraction
        HelperFunctions::printInfo("Maximum candidate epochs: " . fann_get_cascade_max_cand_epochs($ann)); // The maximum candidate epochs determines the maximum number of epochs the input connections to the candidates may be trained before adding a new candidate neuron. The default max candidate epochs is 150.
        HelperFunctions::printInfo("Maximum out epochs: " . fann_get_cascade_max_out_epochs($ann)); // Maximum number of epochs the output connections may be trained after adding a new candidate neuron. The default max out epochs is 150.
        HelperFunctions::printInfo("Minimum candidate epochs: " . fann_get_cascade_min_cand_epochs($ann)); // Minimum number of epochs the input connections to the candidates may be trained before adding a new candidate neuron. The default min candidate epochs is 50.
        HelperFunctions::printInfo("Minimum out epochs: " . fann_get_cascade_min_out_epochs($ann)); // Minimum number of epochs the output connections must be trained after adding a new candidate neuron. The default min out epochs is 50.
        HelperFunctions::printInfo("Number of cascade output stagnation epochs: " . fann_get_cascade_output_stagnation_epochs($ann)); // Number of epochs training is allowed to continue without changing the MSE by a fraction of fann_get_cascade_output_change_fraction(). The default number of cascade output stagnation epochs is 12.
        HelperFunctions::printInfo("Last error number: " . fann_get_errno($ann));
        HelperFunctions::printInfo("Mean square error from in network: " . fann_get_MSE($ann)); // Reads the mean square error from the network. This value is calculated during training or testing and can therefore sometimes be a bit off if the weights have been changed since the last calculation of the value.
        HelperFunctions::printInfo("Sarprop step error shift: " . fann_get_sarprop_step_error_shift($ann)); // Returns the sarprop step error shift. The default step error shift is 1.385.
        HelperFunctions::printInfo("Sarprop step error threshold factor: " . fann_get_sarprop_step_error_threshold_factor($ann)); // The sarprop step error threshold factor. The default factor is 0.1.
        HelperFunctions::printInfo("Success guess amount: {$successGuessAmount} from {$imagesCount}.");

        // Destroys the entire network and properly freeing all the associated memory:
        fann_destroy($ann);
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
