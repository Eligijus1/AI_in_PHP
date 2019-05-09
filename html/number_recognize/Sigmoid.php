<?php

declare(strict_types=1);

namespace number_recognize;

class Sigmoid
{
    /**
     * Bias
     *
     * This will be a one dimensional array (vector) [10]
     *
     * @var array
     */
    private $biasVector;

    /**
     * Weights
     *
     * This will be a two dimensional array (matrix) [784x10]
     *
     * @var array
     */
    private $weights;

    /**
     * Initialise the bias vector and weights as random values between 0 and 1.
     */
    public function __construct()
    {
        $this->biasVector = [];
        $this->weights = [];
        for ($i = 0; $i < 10; $i++) {// NOTE: 10, because we have 10 numbers (0,1...9)
            $this->biasVector[$i] = random_int(1, 1000) / 1000;
            $this->weights[$i] = [];
            for ($j = 0; $j < MnistDataset::IMAGE_SIZE; $j++) {
                $this->weights[$i][$j] = random_int(1, 1000) / 1000;
            }
        }
    }

    public function test(array $input)
    {
        global $numLayers;
        global $layerSize;
        global $alpha;
        global $beta;
        global $weight;
        global $prevWeight;
        global $out;

        //Assign input to 0th layer
        foreach ($input as $key => $value) {
            $out[0][$key] = $value;
        }

        //Assign output for each neuron using sigmoid function:
        for ($i = 1; $i < $numLayers; $i++) {
            for ($j = 0; $j < $layerSize[$i]; $j++) {

                $sum = 0;

                if ($i == 1) {
                    $k = 0;
                    foreach ($out[0] as $key => $value) {
                        $sum += $value * $weight[$i][$j][$k++];
                    }
                } else {
                    for ($k = 0; $k < $layerSize[$i - 1]; $k++) {
                        $sum += $out[$i - 1][$k] * $weight[$i][$j][$k];
                    }
                }

                //Apply bias
                $sum += $weight[$i][$j][$layerSize[$i - 1]];

                //Finally
                $out[$i][$j] = $this->sigmoid($sum);
            }
        }
    }

    /**
     * Training network on the provided
     * training set
     *
     * @param array $trainingSets
     *
     * @return boolean
     */
    public function train(array $trainingSets)
    {
        $numEpochs = 1;

        do {
            if ($this->numEpochs > $this->maxNumEpochs) {
                return false;
            }

            $sumNetworkError = 0;
            foreach ($trainingSets as $trainingSet) {
                $this->network->activate($trainingSet);
                $outputs = $this->network->getOutputs();
                $this->calculateNodeDeltas($trainingSet);
                $this->calculateGradients();
                $this->calculateWeightUpdates();
                $this->applyWeightChanges();
                $sumNetworkError += $this->calculateNetworkError($trainingSet);
            }

            $globalError = $sumNetworkError / count($trainingSets);

            $numEpochs++;
        } while ($globalError > $this->minimumError);

        return true;
    }

    private function sigmoid($t)
    {
        //return 1 / (1 + pow(M_EULER, -$t));// M_EULER	0.57721566490153286061	Euler constant
        //return 1 / (1 + pow(M_E, -$t));// M_E	2.7182818284590452354	e
        return 1 / (1 + exp(-$t));
    }

    private function array_zip(array $a1, array $a2): array
    {
        $out = [];

        for ($i = 0; $i < min(count($a1), count($a2)); $i++) {
            $out[$i] = [$a1[$i], $a2[$i]];
        }

        return $out;
    }

    private function getDerivative($net)
    {
        return $this->sigmoid($net) * (1 - $this->sigmoid($net));
    }

    /**
     * @return array
     */
    private function zip()
    {
        $args = func_get_args();
        $zipped = array();
        $n = count($args);
        for ($i = 0; $i < $n; ++$i) {
            reset($args[$i]);
        }
        while ($n) {
            $tmp = array();
            for ($i = 0; $i < $n; ++$i) {
                if (key($args[$i]) === null) {
                    break 2;
                }
                $tmp[] = current($args[$i]);
                next($args[$i]);
            }
            $zipped[] = $tmp;
        }
        return $zipped;
    }

    /*
     Python:

    def feedforward(self, a):
        """Return the output of the network if "a" is input."""
        for b, w in zip(self.biases, self.weights):
            a = sigmoid(np.dot(w, a)+b)
        return a
     */
}

/*
function sigmoid($x)
{
    $steepness = 0.00069315;
    return 1 / (1 + exp(-$x * $steepness));
}
for ($i = 0; $i <= 10000; ++$i) {
    echo $i, ': ', sigmoid($i), '<br>';
}

*/
