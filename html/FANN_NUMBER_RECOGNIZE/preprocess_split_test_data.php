<?php

require('utils.php');

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

println('Splitting test data into test and validation file...');
split_test_into_val_and_test('test.fann', 'val.fann', 2000);
println('Done!');
