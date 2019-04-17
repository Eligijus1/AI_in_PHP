<?php
declare(strict_types=1);

// Disable the memory limit to avoid "Allowed memory size exhausted" errors:
//ini_set('memory_limit', '-1');

// Set memory limit to 3 Gb (Yes, it eating a lot memory...):
ini_set('memory_limit', '3G');

use number_recognize\helpers\MnistDataSetReaderTesting;
use number_recognize\helpers\MnistImageAsciiGenerator;
use number_recognize\helpers\PerceptronFormulaGenerator;
use number_recognize\helpers\PerceptronTestHelper;
use number_recognize\helpers\PerceptronTrainHelper;
use number_recognize\MnistDataset;
use number_recognize\MnistDataSetReader;
use number_recognize\MnistImageGenerator;
use number_recognize\MnistNeuralNetwork;

require_once 'Perceptron.php';
require_once 'helpers/HelperFunctions.php';
require_once 'helpers/MnistDataSetReaderTesting.php';
require_once 'MnistDataSetReader.php';
require_once 'MnistDataset.php';
require_once 'MnistNeuralNetwork.php';
require_once 'helpers/PerceptronTrainHelper.php';
require_once 'MnistImageGenerator.php';
require_once 'helpers/MnistImageAsciiGenerator.php';
require_once 'helpers/PerceptronTestHelper.php';
require_once 'helpers/BlackWhiteImageSaver.php';
require_once 'helpers/PerceptronFormulaGenerator.php';

// Define constants:
const COLOR_WHITE = "\033[0m";
const COLOR_RED = "\033[31m";
const COLOR_GREEN = "\033[32m";
const trainImagePath = 'data/mnist/train-images.idx3-ubyte';
const trainLabelPath = 'data/mnist/train-labels.idx1-ubyte';
const testImagePath = 'data/mnist/t10k-images.idx3-ubyte';
const testLabelPath = 'data/mnist/t10k-labels.idx1-ubyte';

$BATCH_SIZE = 100;
$STEPS = 1000;

// Check specified arguments:
if (empty($argv[1])) {
    echo "\nERROR: Please specify action.\n";
    return;
}

// Handling first argument:
switch ($argv[1]) {
    // Example: php main.php generate_images
    case 'generate_images':
        MnistDataSetReaderTesting::generateImages(trainImagePath);
        break;

    // Example: php main.php generate_specified_number_images 0
    case 'generate_specified_number_images':
        (new MnistImageGenerator())->generateOneNumberImages(trainImagePath, trainLabelPath, (int)$argv[2]);
        break;

    // Example: php main.php generate_specified_number_images_black_white 0
    case 'generate_specified_number_images_black_white':
        (new MnistImageGenerator())->generateOneNumberImages(trainImagePath, trainLabelPath, (int)$argv[2], true);
        break;

    // Example: php main.php generate_ascii 10
    case 'generate_ascii':
        (new MnistImageAsciiGenerator())->generate(trainImagePath, trainLabelPath, (int)$argv[2]);
        break;

    // Example: php main.php train_perceptron
    case 'train_perceptron':
        (new PerceptronTrainHelper())->train(trainImagePath, trainLabelPath);
        break;

    // Example: php main.php test_perceptron
    case 'test_perceptron':
        (new PerceptronTestHelper())->test(testImagePath, testLabelPath, 0);
        break;

    // Example: php main.php gen_perceptron_formula 0
    case 'gen_perceptron_formula':
        (new PerceptronFormulaGenerator())((int)$argv[2]);
        break;

    // Example:
    case 'train_sigmoid_network':

        // ---------------------- BEGIN -----------------------------------

        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . ' INFO: Loading training dataset... (may take a while)' . PHP_EOL;

        // Loading training data:
        $trainDataset = MnistDataSetReader::fromFiles(trainImagePath, trainLabelPath);

        // Splitting training data to batches ???:
        $batches = $trainDataset->getSize() / $BATCH_SIZE;

        // Inform about training start:
        echo date_format(new DateTime(), 'Y.m.d H:i:s') . " INFO: Starting training..." . PHP_EOL;

        // Define training start time:
        $milliseconds_training_start = round(microtime(true) * 1000);

        // Create neural network object:
        $neuralNetwork = new MnistNeuralNetwork();

        // Training:
        for ($i = 0; $i < $STEPS; $i++) {
            // Retrieve a subset of the dataset as a batch:
            $batch = $trainDataset->getBatch($BATCH_SIZE, $i % $batches);

            //
            $loss = $neuralNetwork->trainingStep($batch, 0.5);
            $averageLoss = $loss / $batch->getSize();
            //$accuracy = calculate_accuracy($neuralNetwork, $testDataset);
            //printf("Step %04d\tAverage Loss %.2f\tAccuracy: %.2f\n", $i + 1, $averageLoss, $accuracy);
        }

        // Information about results:
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Done training in " . (round(microtime(true) * 1000) - $milliseconds) . " milliseconds\n";
        echo date_format(new DateTime(), 'Y.m.d H:i:s') . " INFO: Memory used: " . memory_get_usage(true) . " bytes\n";
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: peak of memory allocated by PHP: " . memory_get_peak_usage(true) . " bytes\n";
        echo date_format(new DateTime(), 'Y.m.d H:i:s') . " INFO: Training data amount: {$trainDataset->getSize()}\n";

        // ---------------------- END -----------------------------------

        break;
    default:
        echo "\nERROR: Unhandled action '" . $argv[1] . "'.\n";
        return;
}

/**
 * Accuracy Evaluation
 *
 * @param MnistNeuralNetwork $neuralNetwork
 * @param MnistDataset       $dataset
 *
 * @return float|int
 */
function calculate_accuracy(MnistNeuralNetwork $neuralNetwork, MnistDataset $dataset)
{
    $size = $dataset->getSize();
    // Loop through all the training examples
    for ($i = 0, $correct = 0; $i < $size; $i++) {
        $image = $dataset->getImage($i);
        $label = $dataset->getLabel($i);
        $activations = $neuralNetwork->hypothesis($image);

        // Our prediction is index containing the maximum probability:
        $prediction = array_search(max($activations), $activations);
        if ($prediction == $label) {
            $correct++;
        }
    }

    // Percentage of correct predictions is the accuracy:
    return $correct / $size;
}
