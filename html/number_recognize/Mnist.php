<?php

declare(strict_types=1);

use number_recognize\MnistDataSetReader;
use number_recognize\MnistDataSetReaderTesting;

require_once 'MnistDataSetReaderTesting.php';
require_once 'MnistDataSetReader.php';

$BATCH_SIZE = 100;
$STEPS = 1000;

// Load Training Dataset
$trainImagePath = 'data/train-images-idx3-ubyte';
$trainLabelPath = 'data/train-labels-idx1-ubyte';

echo 'Loading training dataset (may take a while)...' . PHP_EOL;

//try {
//    $trainDataset = MnistDataSetReader::fromFiles($trainImagePath, $trainLabelPath);
//} catch (Exception $e) {
//    echo "ERROR: {$e->getCode()} {$e->getMessage()}" . PHP_EOL;
//}

MnistDataSetReaderTesting::readImages('data\t10k-images.idx3-ubyte');
