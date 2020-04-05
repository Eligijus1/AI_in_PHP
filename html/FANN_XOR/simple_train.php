<?php
/**
 * XOR - Exclusive or or exclusive disjunction is a logical operation that outputs true only when inputs differ
 *       (one is true, the other is false).
 */

// Define application start time:
$milliseconds = round(microtime(true) * 1000);

// The total number of layers including the input and the output layer:
$num_layers = 3;

// Number of neurons in the first layer:
$num_input = 2;

// Number of neurons in the second layer (experimental way to decide):
$num_neurons_hidden = 3;

// Number of neurons in the 3rd (Third) layer - output:
$num_output = 1;

// It mean 1 error in 1000 samples, like 1 mistake in 1000 tries:
$desired_error = 0.001;

// The maximum number of epochs the training should continue (experimental way to decide):
$max_epochs = 500000;

// The number of epochs between calling a user function.
// A value of zero means that user function is not called.
// One Epoch is when an ENTIRE dataset is passed forward and backward through the neural network only ONCE.
$epochs_between_reports = 1000;

/* Creates a standard fully connected back propagation neural network
 * There will be a bias neuron in each layer (except the output layer),
 * and this bias neuron will be connected to all neurons in the next layer.
 * When running the network, the bias nodes always emits 1.
 */
$ann = fann_create_standard($num_layers, $num_input, $num_neurons_hidden, $num_output);

// Check if returns a neural network resource:
if ($ann) {
    // Sets the activation function for all of the hidden layers (FANN_SIGMOID_SYMMETRIC - Symmetric sigmoid activation function, aka. tanh.):
    fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC);

    // Sets the activation function for the output layer:
    fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC);

    // Define the file containing train data:
    $filename = dirname(__FILE__) . "/xor.data";

    // Trains on an entire dataset, which is read from file, for a period of time:
    if (fann_train_on_file($ann, $filename, $max_epochs, $epochs_between_reports, $desired_error)) {
        // Saves the entire network to a configuration file:
        fann_save($ann, dirname(__FILE__) . "/xor_float.net");
        echo "INFO: saved to file " . dirname(__FILE__) . "/xor_float.net" . PHP_EOL;
    } else {
        echo "ERROR: fann_train_on_file returned false." . PHP_EOL;
    }

    // Destroys the entire network and properly freeing all the associated memory:
    fann_destroy($ann);
} else {
    echo "ERROR: ann is empty." . PHP_EOL;
}

// Inform about application finish and print time used:
echo "INFO: Done simple_train.php in " . (round(microtime(true) * 1000) - $milliseconds) . " milliseconds" . PHP_EOL;
