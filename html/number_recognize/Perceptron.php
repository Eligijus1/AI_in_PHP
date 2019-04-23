<?php

declare(strict_types=1);

namespace number_recognize;

use InvalidArgumentException;
use RuntimeException;

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
    private $weightVector;
    private $iterations = 0;
    private $errorSum = 0;
    private $iterationError = 0;
    private $output = null;

    /**
     * @param int   $vectorLength The number of input signals
     * @param float $bias         Bias factor
     *
     * @throws InvalidArgumentException
     */
    public function __construct($vectorLength, $bias = 0.0)
    {
        if ($vectorLength < 1) {
            throw new InvalidArgumentException();
        }

        $this->vectorLength = $vectorLength;
        $this->bias = $bias;

        for ($i = 0; $i < $this->vectorLength; $i++) {
            $this->weightVector[$i] = 0;
        }
    }

    public function getOutput()
    {
        if (is_null($this->output)) {
            throw new RuntimeException();
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
     * @return int
     */
    public function getBias()
    {
        return $this->bias;
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

        $testResult = $this->dotProduct($inputVector) + $this->bias;

        $this->output = $testResult > 0 ? 1 : 0;

        return $this->output;
    }

    /**
     * Perceptron training method, responsible define weights and bias of this perceptron
     * by training data.
     *
     * @param array $inputVector array of input signals
     * @param bool  $outcomeBool what output signal should by.
     *
     * @throws InvalidArgumentException
     */
    public function train(array $inputVector, bool $outcomeBool): void
    {
        $outcome = ($outcomeBool ? 1 : 0);

        // Make sure, that input parameters are valid
        if (!is_array($inputVector) || !($outcome == 0 || $outcome == 1)) {
            throw new InvalidArgumentException();
        }

        // Update perceptron iteration number:
        $this->iterations += 1;

        // Testing what value returns network:
        $output = $this->test($inputVector);

        // Loop all array items:
        for ($i = 0; $i < $this->vectorLength; $i++) {
            $this->weightVector[$i] = $this->weightVector[$i] + ((int)$outcome - (int)$output) * $inputVector[$i];
        }

        // Define bias
        $this->bias = $this->bias + ((int)$outcome - (int)$output);
        $this->errorSum += (int)$outcome - (int)$output;
        $this->iterationError = 1 / $this->iterations * $this->errorSum;
    }

    public function getFormula(): string
    {
        $formula = "";

        foreach ($this->weightVector as $key => $weight) {
            $formula .= (empty($formula) ? "" : " + ") . "x{$key}*{$weight}";
        }

        return "result = (({$formula}) * {$this->bias}) > 0 ? 1 : 0";
    }

    private function dotProduct(array $inputVector): float
    {
        // Calculate the sum of values in an array:
        return array_sum(
            array_map(
                function ($a, $b) {
                    return $a * $b;
                },
                $this->weightVector,
                $inputVector
            )
        );
    }
}
