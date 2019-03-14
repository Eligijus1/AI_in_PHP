<?php

declare(strict_types=1);

// Disable the memory limit to avoid "Allowed memory size exhausted" errors:
//ini_set('memory_limit', '-1');

// Set memory limit to 3 Gb (Yes, it eating a lot memory...):
ini_set('memory_limit', '3G');


use number_recognize\MnistDataSetReader;
use number_recognize\MnistDataSetReaderTesting;

require_once 'MnistDataSetReaderTesting.php';
require_once 'MnistDataSetReader.php';
require_once 'MnistDataset.php';

// Attempt to clear console:
//system('cls');
//system('clear');

const COLOR_WHITE = "\033[0m";
const COLOR_RED = "\033[31m";
const COLOR_GREEN = "\033[32m";

$BATCH_SIZE = 100;
$STEPS = 1000;

// Load Training Dataset
$trainImagePath = 'data/mnist/train-images.idx3-ubyte';
$trainLabelPath = 'data/mnist/train-labels.idx1-ubyte';

// Check specified arguments:
if (empty($argv[1])) {
    echo "\nERROR: Please specify action.\n";
    return;
}

// Handling first argument:
switch ($argv[1]) {
    case 'generate_images':
        MnistDataSetReaderTesting::generateImages('data/mnist/t10k-images.idx3-ubyte');
        break;
    case 'train_network':

        // ---------------------- BEGIN -----------------------------------

        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . 'INFO: Loading training dataset... (may take a while)' . PHP_EOL;

        // Loading training data:
        $trainDataset = MnistDataSetReader::fromFiles($trainImagePath, $trainLabelPath);

        // Begin Training
        $batches = $trainDataset->getSize() / $BATCH_SIZE;

        // Information about results:
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Done training in " . (round(microtime(true) * 1000) - $milliseconds) . " milliseconds\n";
        echo date_format(new \DateTime(), 'Y.m.d H:i:s') . " INFO: Memory used: " . memory_get_usage(true) . " bytes\n";
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: peak of memory allocated by PHP: " . memory_get_peak_usage(true) . " bytes\n";

        // ---------------------- END -----------------------------------

        break;
    default:
        echo "\nERROR: Unhandled action '" . $argv[1] . "'.\n";
        return;
}
