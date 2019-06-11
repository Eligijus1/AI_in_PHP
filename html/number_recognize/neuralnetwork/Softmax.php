<?php

declare(strict_types=1);

namespace number_recognize\neuralnetwork;

use Exception;

class Softmax
{
    /**
     * Learning rate is a hyper-parameter that controls how much we are adjusting the weights of our network with
     * respect the loss gradient. The lower the value, the slower we travel along the downward slope. While this
     * might be a good idea (using a low learning rate) in terms of making sure that we do not miss any local minima,
     * it could also mean that we’ll be taking a long time to converge — especially if we get stuck on a plateau region.
     *
     * NOTE: empiric variable defined in constructor. Need play, to find perfect value. Value examples: 0.7, 0.9 ...
     *
     * @var float
     */
    private $learningRate;

    // This will be a one dimensional array (vector) [10]:
    private $b;

    // This will be a two dimensional array (matrix) [784x10]:
    private $W;

    // Input layer is equal to images size (width x height):
    private const IMAGE_SIZE = 28 * 28;

    // Output layer is equal to possible results (0,1,2...9):
    private const LABELS = 10;

    /**
     * @var int
     */
    private $epochsNumber;

    /**
     * Initialise the bias vector and weights as random values between 0 and 1.
     *
     * @param float $learningRate
     * @param int   $epochsNumber
     *
     * @throws Exception
     */
    public function __construct(float $learningRate, int $epochsNumber)
    {
        $this->b = [];
        $this->W = [];
        $this->learningRate = $learningRate;
        $this->epochsNumber = $epochsNumber;

        for ($i = 0; $i < self::LABELS; $i++) {
            $this->b[$i] = random_int(1, 1000) / 1000;
            $this->W[$i] = [];
            for ($j = 0; $j < self::IMAGE_SIZE; $j++) {
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
        for ($i = 0; $i < self::LABELS; $i++) {
            $activations[$i] = $this->b[$i];
            for ($j = 0; $j < self::IMAGE_SIZE; $j++) {
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

        for ($i = 0; $i < self::LABELS; $i++) {
            // Uses the derivative of the softmax function
            $bGradPart = ($i === $label) ? $activations[$i] - 1 : $activations[$i];
            for ($j = 0; $j < self::IMAGE_SIZE; $j++) {
                // Gradient is the product of the bias gradient and the input activation
                $WGrad[$i][$j] += $bGradPart * $image[$j];
            }
            $bGrad[$i] += $bGradPart;
        }

        // Cross entropy:
        return 0 - log($activations[$label]);
    }

    /**
     * Perform one step of gradient descent on the neural network using the
     * provided data.
     *
     * Returns the total loss for the network on the provided dataset.
     *
     * @param array $images
     * @param array $labels
     *
     * @return float
     */
    public function trainingStep(array $images, array $labels): float
    {
        // Zero init the gradients
        $bGrad = array_fill(0, self::LABELS, 0);
        $WGrad = array_fill(0, self::LABELS, array_fill(0, self::IMAGE_SIZE, 0));
        $totalLoss = 0;
        $size = count($images);

        // Calculate the gradients and loss:
        for ($i = 0; $i < $size; $i++) {
            $totalLoss += $this->gradientUpdate($images[$i], $bGrad, $WGrad, $labels[$i]);
        }

        // Adjust the weights and bias vector using the gradient and the learning rate:
        for ($i = 0; $i < self::LABELS; $i++) {
            $this->b[$i] -= $this->learningRate * $bGrad[$i] / $size;
            for ($j = 0; $j < self::IMAGE_SIZE; $j++) {
                $this->W[$i][$j] -= $this->learningRate * $WGrad[$i][$j] / $size;
            }
        }

        return $totalLoss;
    }

    public function getLearningRate(): float
    {
        return $this->learningRate;
    }

    public function getEpochsNumber(): int
    {
        return $this->epochsNumber;
    }

    /**
     * @param int $epochsNumber
     */
    public function setEpochsNumber(int $epochsNumber): void
    {
        $this->epochsNumber = $epochsNumber;
    }
}
