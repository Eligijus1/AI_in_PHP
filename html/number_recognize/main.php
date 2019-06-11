<?php
declare(strict_types=1);

// Disable the memory limit to avoid "Allowed memory size exhausted" errors:
//ini_set('memory_limit', '-1');

// Set memory limit to 6 Gb (Yes, it eating a lot memory...):
ini_set('memory_limit', '6G');

use number_recognize\helpers\HelperFunctions;
use number_recognize\helpers\MnistDataSetReaderTesting;
use number_recognize\helpers\MnistImageAsciiGenerator;
use number_recognize\helpers\MnistImageGeneratorHelper;
use number_recognize\helpers\PerceptronFormulaGenerator;
use number_recognize\helpers\PerceptronTestHelper;
use number_recognize\helpers\PerceptronTrainHelper;
use number_recognize\helpers\SigmoidTestHelper;
use number_recognize\helpers\SigmoidTrainHelper;
use number_recognize\helpers\SoftmaxTestHelper;
use number_recognize\helpers\SoftmaxTrainHelper;

require_once 'neuralnetwork/Perceptron.php';
require_once 'neuralnetwork/Sigmoid.php';
require_once 'neuralnetwork/Softmax.php';
require_once 'helpers/HelperFunctions.php';
require_once 'helpers/MnistDataSetReaderTesting.php';
require_once 'helpers/PerceptronTrainHelper.php';
require_once 'helpers/MnistImageAsciiGenerator.php';
require_once 'helpers/PerceptronTestHelper.php';
require_once 'helpers/BlackWhiteImageSaver.php';
require_once 'helpers/PerceptronFormulaGenerator.php';
require_once 'helpers/MnistImageGeneratorHelper.php';
require_once 'helpers/SigmoidTrainHelper.php';
require_once 'helpers/SigmoidTestHelper.php';
require_once 'helpers/SoftmaxTrainHelper.php';
require_once 'helpers/SoftmaxTestHelper.php';

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
        (new MnistImageGeneratorHelper())->generateOneNumberImages(trainImagePath, trainLabelPath, (int)$argv[2]);
        break;

    // Example: php main.php generate_specified_number_images_black_white 0
    case 'generate_specified_number_images_black_white':
        //(new MnistImageGenerator())->generateOneNumberImages(trainImagePath, trainLabelPath, (int)$argv[2], true);
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

    // Example: php main.php train_sigmoid
    case 'train_sigmoid':
        (new SigmoidTrainHelper())->train(trainImagePath, trainLabelPath, 0.2, 0.7, 20);
        break;

    // Example: php main.php test_sigmoid
    case 'test_sigmoid':
        (new SigmoidTestHelper())->test(testImagePath, testLabelPath,
            'C:\Projects\AI_in_PHP\html\number_recognize\data\train_sigmoid\Backups\00065_sigmoid_9451.dat');
        break;

    // Example: php main.php train_softmax
    case 'train_softmax':
        (new SoftmaxTrainHelper())->train(trainImagePath, trainLabelPath, 0.1);
        break;

    // Example: php main.php test_softmax
    case 'test_softmax':
        (new SoftmaxTestHelper())->test(testImagePath, testLabelPath,
            'C:\Projects\AI_in_PHP\html\number_recognize\data\train_softmax\softmax.dat');
        break;
    default:
        HelperFunctions::printError("Unhandled action '{$argv[1]}'");
        return;
}
