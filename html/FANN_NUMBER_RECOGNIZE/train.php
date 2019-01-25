<?php

declare(strict_types=1);

// Define application start time:
$milliseconds = round(microtime(true) * 1000);

// The total number of layers including the input and the output layer:
$num_layers = 3;

// Number of neurons in the first layer:
$num_input = 28*28; // 28x28px

// Number of neurons in the second layer (experimental way to decide):
$num_neurons_hidden = 3;

// Number of neurons in the 3rd (Third) layer - output:
$num_output = 10;// 1 neuron for every digit

// It mean 1 error in 1000 samples, like 1 mistake in 1000 tries:
$desired_error = 0.001;

// The maximum number of epochs the training should continue (experimental way to decide):
$max_epochs = 500000;
