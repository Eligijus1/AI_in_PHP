<?php

declare(strict_types=1);

namespace number_recognize;

class MnistNeuralNetwork
{
    // This will be a one dimensional array (vector) [10]:
    private $b;

    // This will be a two dimensional array (matrix) [784x10]:
    private $W;

    /**
     * Initialise the bias vector and weights as random values between 0 and 1.
     */
    public function __construct()
    {
        $this->b = [];
        $this->W = [];
        for ($i = 0; $i < MnistDataset::LABELS; $i++) {
            $this->b[$i] = random_int(1, 1000) / 1000;
            $this->W[$i] = [];
            for ($j = 0; $j < MnistDataset::IMAGE_SIZE; $j++) {
                $this->W[$i][$j] = random_int(1, 1000) / 1000;
            }
        }
    }

    /**
     * The softmax layer maps an array of activations to a probability vector.
     *
     * @param array $activations
     *
     * @return array
     */
    private function softmax(array $activations): array
    {
        // Normalising with the max activation makes the computation more numerically stable
        $max = max($activations);
        $activations = array_map(function ($a) use ($max) {
            return exp($a - $max);
        }, $activations);
        $sum = array_sum($activations);
        return array_map(function ($a) use ($sum) {
            return $a / $sum;
        }, $activations);
    }

//    /**
//     * Softmax calculation for PHP (useful for logistic classifications)
//     *
//     * @param array $v
//     *
//     * @return array
//     */
//    function softmax2(array $v)
//    {
//
//        //Just in case values are passed in as string, apply floatval
//        $v = array_map('exp', array_map('floatval', $v));
//        $sum = array_sum($v);
//
//        foreach ($v as $index => $value) {
//            $v[$index] = $value / $sum;
//        }
//
//        return $v;
//    }

    /**
     * Forward propagate through the neural network to calculate the activation
     * vector for an image.
     *
     * @param array $image
     *
     * @return array
     */
    public function hypothesis(array $image): array
    {
        $activations = [];
        // Computes: Wx + b
        for ($i = 0; $i < MnistDataset::LABELS; $i++) {
            $activations[$i] = $this->b[$i];
            for ($j = 0; $j < MnistDataset::IMAGE_SIZE; $j++) {
                $activations[$i] += $this->W[$i][$j] * $image[$j];
            }
        }
        return $this->softmax($activations);
    }

    /**
     * Calculate the gradient adjustments on a single training example (image)
     * from the dataset.
     *
     * Returns the contribution to the loss value from this example.
     *
     * @param array $image
     * @param array $bGrad
     * @param array $WGrad
     * @param int   $label
     *
     * @return float
     */
    private function gradientUpdate(array $image, array &$bGrad, array &$WGrad, int $label): float
    {
        $activations = $this->hypothesis($image);
        for ($i = 0; $i < MnistDataset::LABELS; $i++) {
            // Uses the derivative of the softmax function
            $bGradPart = ($i === $label) ? $activations[$i] - 1 : $activations[$i];
            for ($j = 0; $j < MnistDataset::IMAGE_SIZE; $j++) {
                // Gradient is the product of the bias gradient and the input activation
                $WGrad[$i][$j] += $bGradPart * $image[$j];
            }
            $bGrad[$i] += $bGradPart;
        }
        // Cross entropy
        return 0 - log($activations[$label]);
    }

    /**
     * Perform one step of gradient descent on the neural network using the
     * provided dataset.
     *
     * Returns the total loss for the network on the provided dataset.
     *
     * @param MnistDataset $dataset
     * @param float        $learningRate
     *
     * @return float
     */
    public function trainingStep(MnistDataset $dataset, float $learningRate): float
    {
        // Zero init the gradients
        $bGrad = array_fill(0, MnistDataset::LABELS, 0);
        $WGrad = array_fill(0, MnistDataset::LABELS, array_fill(0, MnistDataset::IMAGE_SIZE, 0));
        $totalLoss = 0;
        $size = $dataset->getSize();

        // Calculate the gradients and loss:
        for ($i = 0; $i < $size; $i++) {
            $totalLoss += $this->gradientUpdate($dataset->getImage($i), $bGrad, $WGrad, $dataset->getLabel($i));
        }

        // Adjust the weights and bias vector using the gradient and the learning rate:
        for ($i = 0; $i < MnistDataset::LABELS; $i++) {
            $this->b[$i] -= $learningRate * $bGrad[$i] / $size;
            for ($j = 0; $j < MnistDataset::IMAGE_SIZE; $j++) {
                $this->W[$i][$j] -= $learningRate * $WGrad[$i][$j] / $size;
            }
        }

        return $totalLoss;
    }
}
