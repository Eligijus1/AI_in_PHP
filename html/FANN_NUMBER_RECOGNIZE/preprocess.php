<?php

declare(strict_types=1);

require('utils.php');

function convert_mnist_to_fann($imgfile, $labelf, $outf)
{
    // open handlers for the img, label and out file
    $f = fopen($imgfile, 'rb');
    $l = fopen($labelf, 'rb');
    $o = fopen($outf, 'w');
    // we don't care about the first 4 bytes (magic number)
    fread($f, 4);
    // read the number of samples
    $n_samples = freadint($f);
    // read the number of img cols and rows
    $nrows = freadint($f);
    $ncols = freadint($f);
    // discard first 8 bytes of the label file (magic number and samples count)
    fread($l, 8);
    // write the fann file header
    fwrite($o, sprintf('%d %d %d ', $n_samples, $nrows * $ncols, 10));
    fwrite($o, PHP_EOL);
    // loop through all samples
    foreach (range(1, $n_samples) as $n) {
        // get the label for this sample
        $idLabel = freadchar($l);
        // create an one hot encode placeholder
        $labels = array_fill(0, 10, 0.1);
        // set the correct label with a higher value
        $labels[$idLabel] = 0.9;
        // loop through all pixel values in a sample
        foreach (range(1, ($ncols * $nrows)) as $j) {
            fwrite($o, sprintf('%f ', freadchar($f) / 255.));
        }
        fwrite($o, PHP_EOL);
        // writing the label
        fwrite($o, implode(' ', $labels));
        fwrite($o, PHP_EOL);
    }
    // close the file handlers
    fclose($f);
    fclose($l);
    fclose($o);
}

function split_test_into_val_and_test($testFannF, $valf, $howManySamplesValidation = 0)
{
    $tf = fopen($testFannF, 'r');
    $vf = fopen($valf, 'w');
    $temptf = fopen('tmp_test.fann', 'w');
    $header = fgets($tf);
    $total = explode(' ', $header)[0];
    $final_test_size = $total - $howManySamplesValidation;
    fwrite($vf, str_replace('10000', $howManySamplesValidation, $header));
    fwrite($temptf, str_replace('10000', $final_test_size, $header));
    $samplesCount = 1;
    while (!feof($tf)) {
        $f = ($samplesCount / 2 > $final_test_size) ? $vf : $temptf;
        $line = fgets($tf);
        fwrite($f, $line);
        $samplesCount++;
    }
    fclose($tf);
    fclose($vf);
    fclose($temptf);
    rename('tmp_test.fann', $testFannF);
}

println('Converting mninst binary train data to fann format...');
convert_mnist_to_fann('data/train-images-idx3-ubyte', 'data/train-labels-idx1-ubyte', 'train.fann');
println('Done!');
println('Converting mninst binary test data to fann format...');
convert_mnist_to_fann('data/t10k-images-idx3-ubyte', 'data/t10k-labels-idx1-ubyte', 'test.fann');
println('Done!');
println('Splitting test data into test and validation file...');
split_test_into_val_and_test('test.fann', 'val.fann', 2000);
println('Done!');

