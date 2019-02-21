<?php

declare(strict_types=1);

use number_recognize\MnistDataSetReader;
use number_recognize\MnistDataSetReaderTesting;

require_once 'MnistDataSetReaderTesting.php';
require_once 'MnistDataSetReader.php';

//system('cls');
//system('clear');

const COLOR_WHITE = "\033[0m";
const COLOR_RED = "\033[31m";
const COLOR_GREEN = "\033[32m";

$BATCH_SIZE = 100;
$STEPS = 1000;

// Load Training Dataset
$trainImagePath = 'data/mnist/train-images-idx3-ubyte';
$trainLabelPath = 'data/mnist/train-labels-idx1-ubyte';

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
    default:
		echo "\nERROR: Unhandled action '" . $argv[1] . "'.\n";
		return;
}


