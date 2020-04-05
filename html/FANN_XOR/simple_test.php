<?php
// Define application start:
$milliseconds = round(microtime(true) * 1000);

// Define train file location:
$train_file = (dirname(__FILE__) . "/xor_float.net");

// Interrupt application if train file not exist:
if (!is_file($train_file)) {
    die("The file xor_float.net has not been created! Please run simple_train.php to generate it");
}

// Constructs a backpropagation neural network from a configuration file:
$ann = fann_create_from_file($train_file);

// Check if neural network created:
if (!$ann) {
    die("ANN could not be created");
}

// Array of input values:
$input = array(-1, 1);

// Will run input through the neural network, returning an array of outputs,
// the number of which being equal to the number of neurons in the output layer:
$calc_out = fann_run($ann, $input);

// Print information:
printf("INFO: xor test (%f,%f) -> %f" . PHP_EOL, $input[0], $input[1], $calc_out[0]);
echo "INFO: Number of neurons in each layer in the network: " . PHP_EOL . print_r(fann_get_layer_array($ann), true);
echo "INFO: Learning momentum: " . fann_get_learning_momentum($ann) . PHP_EOL;
// NOTE: The learning momentum can be used to speed up FANN_TRAIN_INCREMENTAL training. A too high momentum will
// however not benefit training. Setting momentum to 0 will be the same as not using the momentum parameter. The
// recommended value of this parameter is between 0.0 and 1.0.
// The default momentum is 0.
echo "INFO: Learning rate: " . fann_get_learning_rate($ann) . PHP_EOL;
// NOTE: The learning rate is used to determine how aggressive training should be for some of the training algorithms
// (FANN_TRAIN_INCREMENTAL, FANN_TRAIN_BATCH, FANN_TRAIN_QUICKPROP). Do however note that it is not used in FANN_TRAIN_RPROP.
// The default learning rate is 0.7.

// Destroys the entire network and properly freeing all the associated memory:
fann_destroy($ann);

// Inform about application finish and print time used:
echo "INFO: Done simple_test.php in " . (round(microtime(true) * 1000) - $milliseconds) . " milliseconds" . PHP_EOL;
