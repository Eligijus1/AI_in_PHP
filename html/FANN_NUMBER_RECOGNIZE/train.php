<?php

require('utils.php');

// Define application start time:
$milliseconds = round(microtime(true) * 1000);

// The total number of layers including the input and the output layer:
$num_layers = 3;

// Number of neurons in the first layer:
$num_input = 784;

// Number of neurons in the second layer (experimental way to decide):
$num_neurons_hidden = 256;

// Number of neurons in the 3rd (Third) layer - output:
$num_output = 10;

// It mean 1 error in 10000 samples, like 1 mistake in 10000 tries:
$learning_rate = 0.0001;

// The maximum number of epochs the training should continue (experimental way to decide):
$max_epochs = 150;

// The number of epochs between calling a user function.
// A value of zero means that user function is not called.
$epochs_between_reports = 1;

// Create directory for network saving:
if (!file_exists('checkpoints')) {
    mkdir('checkpoints');
}

/* Creates a standard fully connected back propagation neural network
 * There will be a bias neuron in each layer (except the output layer),
 * and this bias neuron will be connected to all neurons in the next layer.
 * When running the network, the bias nodes always emits 1.
 */
$nn = fann_create_standard($num_layers, $num_input, $num_neurons_hidden, $num_output);

// Handling training fail:
if (!$nn) {
    quit("ERROR: Error to get NN instance");
}

println('Training on MNIST... ');

// Sets the activation function for all of the hidden layers (FANN_SIGMOID_SYMMETRIC
// - Symmetric sigmoid activation function, aka. tanh.):
fann_set_activation_function_hidden($nn, FANN_SIGMOID);

// Sets the activation function for the output layer:
fann_set_activation_function_output($nn, FANN_SIGMOID);

// Reads a file that stores testing data:
$testData = fann_read_train_from_file(dirname(__FILE__) . '/test.fann');

// Sets the callback function for use during training.
// It means that it is called from fann_train_on_data() or fann_train_on_file().
fann_set_callback($nn,
    function ($nn, $train, $max_epochs, $epochs_between_reports, $desired_error, $epoch) use ($testData) {
        println('Epoch: ' . $epoch);
        println('Loss: ' . fann_test_data($nn, $testData));
        return true;
    });

// Training data file:
$filename = dirname(__FILE__) . "/train.fann";

// // Trains on an entire dataset, which is read from file, for a period of time:
if (fann_train_on_file($nn, $filename, $max_epochs, $epochs_between_reports, $learning_rate)) {
    // Saves the entire network to a configuration file:
    fann_save($nn, dirname(__FILE__) . "/checkpoints/mnist.net");
}

// Destroys the entire network and properly freeing all the associated memory:
fann_destroy($nn);

// Inform about application finish and print time used:
echo "INFO: Done simple_train.php in " . (round(microtime(true) * 1000) - $milliseconds) ." milliseconds<br>";
