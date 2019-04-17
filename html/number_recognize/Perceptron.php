<?php

declare(strict_types=1);

namespace number_recognize;

use InvalidArgumentException;

/**
 * Perceptron for more information see:
 * http://en.wikipedia.org/wiki/Perceptron
 *
 * NOTE: in programming mathematical vector is a 1-dimensional array.
 */
class Perceptron
{
    private $vectorLength;
    private $bias;
    private $learningRate;
    private $weightVector;
    private $iterations = 0;
    private $errorSum = 0;
    private $iterationError = 0;
    private $output = null;

    /**
     * @param int   $vectorLength The number of input signals
     * @param float $bias         Bias factor
     * @param float $learningRate The learning rate 0 < x <= 1
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($vectorLength, $bias = 0.0, $learningRate = .5)
    {
        if ($vectorLength < 1) {
            throw new \InvalidArgumentException();
        } elseif ($learningRate <= 0 || $learningRate > 1) {
            throw new \InvalidArgumentException();
        }

        $this->vectorLength = $vectorLength;
        $this->bias = $bias;
        $this->learningRate = $learningRate;

        for ($i = 0; $i < $this->vectorLength; $i++) {
            //$this->weightVector[$i] = rand() / getrandmax() * 2 - 1;
            $this->weightVector[$i] = 0;
        }
    }

    public function getOutput()
    {
        if (is_null($this->output)) {
            throw new \RuntimeException();
        } else {
            return $this->output;
        }
    }

    /**
     * @return array
     */
    public function getWeightVector()
    {
        return $this->weightVector;
    }

    /**
     * @param array $weightVector
     *
     * @throws \InvalidArgumentException
     */
    public function setWeightVector($weightVector)
    {
        if (!is_array($weightVector) || count($weightVector) != $this->vectorLength) {
            throw new \InvalidArgumentException();
        }
        $this->weightVector = $weightVector;
    }

    /**
     * @return int
     */
    public function getBias()
    {
        return $this->bias;
    }

    /**
     * @param float $bias
     *
     * @throws \InvalidArgumentException
     */
    public function setBias($bias)
    {
        if (!is_numeric($bias)) {
            throw new \InvalidArgumentException();
        }
        $this->bias = $bias;
    }

    /**
     * @return float
     */
    public function getLearningRate()
    {
        return $this->learningRate;
    }

    /**
     * @param float $learningRate
     *
     * @throws \InvalidArgumentException
     */
    public function setLearningRate($learningRate)
    {
        if (!is_numeric($learningRate) || $learningRate <= 0 || $learningRate > 1) {
            throw new \InvalidArgumentException();
        }
        $this->learningRate = $learningRate;
    }

    /**
     * @return int
     */
    public function getIterationError()
    {
        return $this->iterationError;
    }

    /**
     * @param int[] $inputVector
     *
     * @return int (0 for false, 1 = true)
     * @throws InvalidArgumentException
     */
    public function test(array $inputVector): int
    {
        // Checking input parameter, to make sure, that input values are correct:
        if (!is_array($inputVector) || count($inputVector) != $this->vectorLength) {
            throw new InvalidArgumentException();
        }

        $testResult = $this->dotProduct($this->weightVector, $inputVector) + $this->bias;

        $this->output = $testResult > 0 ? 1 : 0;

        return $this->output;
    }

    /**
     * Perceptron training method, responsible define weights and bias of this perceptron
     * by training data.
     *
     * @param array $inputVector array of input signals
     * @param bool  $outcomeBool
     *
     * @throws \InvalidArgumentException
     */
    public function train(array $inputVector, bool $outcomeBool): void
    {
        $outcome = ($outcomeBool ? 1 : 0);

        // Make sure, that input parameters are valid
        if (!is_array($inputVector) || !($outcome == 0 || $outcome == 1)) {
            throw new \InvalidArgumentException();
        }

        // Update perceptron iteration number:
        $this->iterations += 1;

        // Testing what value returns network:
        $output = $this->test($inputVector);

        // Loop all array items:
        for ($i = 0; $i < $this->vectorLength; $i++) {
            // Define weights:
            $this->weightVector[$i] =
                $this->weightVector[$i] + $this->learningRate * ((int)$outcome - (int)$output) * $inputVector[$i];
        }

        // Define bias
        $this->bias = $this->bias + ((int)$outcome - (int)$output);
        $this->errorSum += (int)$outcome - (int)$output;
        $this->iterationError = 1 / $this->iterations * $this->errorSum;
    }

    /**
     * @param array $vector1
     * @param array $vector2
     *
     * @return number
     */
    private function dotProduct($vector1, $vector2)
    {
        return array_sum(
            array_map(
                function ($a, $b) {
                    return $a * $b;
                },
                $vector1,
                $vector2
            )
        );
    }
}
