<?php
require('utils.php');

// Define application start:
$milliseconds = round(microtime(true) * 1000);

// Define network configuration file:
$modelFile = dirname(__FILE__) . '/checkpoints/mnist.net';
if (!file_exists($modelFile)) {
    quit('MNIST model not found!');
}

// Constructs a backpropagation neural network from a configuration file:
$nn = fann_create_from_file($modelFile);

// Check if neural network created:
if ($nn) {
    $valFile = dirname(__FILE__) . '/val.fann';
    if (!file_exists($valFile)) {
        quit($valFile . ' not found!');
    }

    println('Running inference on ' . $valFile);
    $features = [];
    $labels = [];
    $errors = 0;
    $correct = 0;

    // Open the val file for inference:
    if ($file = fopen($valFile, "r")) {
        while (!feof($file)) {
            $line = trim(fgets($file));
            $line = explode(' ', $line);
            $lineSize = count($line);

            // If the line is an input...
            if ($lineSize == fann_get_num_input($nn)) {
                $features = $line;
            } else {
                if ($lineSize == fann_get_num_output($nn)) { // ...if is an output
                    $labels = $line;
                    $output = fann_run($nn, $features);
                    $pred = argmax($output);
                    $true_pred = argmax($labels);
                    $confidence = amax($output);

                    // Check if guess value is equal to real:
                    if ($true_pred != $pred) {
                        $errors += 1;

                        // Print info:
                        println('I think this number is ' . $pred . ' with ' . round($confidence * 100, 2) . '% confidence');
                        println('REAL VALUE: ' . $true_pred);
                    } else {
                        $correct += 1;
                    }
                    println('');
                }
            }
        }
        fclose($file);
    }

    fann_destroy($nn);

    $total = $errors + $correct;

    // Output result:
    println('Total samples: ' . $total);
    println('Errors: ' . $errors);
    println('Correct: ' . $correct);
    println('Accuracy: ' . ($correct / $total));
    println('Total time: ' . ($correct / $total) . ' milliseconds');
}
