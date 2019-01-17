<?php
// Define application start:
$milliseconds = round(microtime(true) * 1000);

// Define train file location:
$train_file = (dirname(__FILE__) . "/xor_float.net");

// Interrupt application if train file not exist:
if (!is_file($train_file))
    die("The file xor_float.net has not been created! Please run simple_train.php to generate it");

// Constructs a backpropagation neural network from a configuration file:
$ann = fann_create_from_file($train_file);

// Check if neural network created:
if (!$ann)
    die("ANN could not be created");

// Array of input values:
$input = array(-1, 1);

// Will run input through the neural network, returning an array of outputs,
// the number of which being equal to the number of neurons in the output layer:
$calc_out = fann_run($ann, $input);
printf("INFO: xor test (%f,%f) -> %f\n", $input[0], $input[1], $calc_out[0]);
echo "<br>";

// Destroys the entire network and properly freeing all the associated memory:
fann_destroy($ann);

// Inform about application finish and print time used:
echo "INFO: Done simple_test.php in " . (round(microtime(true) * 1000) - $milliseconds) ." milliseconds<br>";
