<?php

declare(strict_types=1);

namespace number_recognize\neuralnetwork;

class Sigmoid
{
    private $networkLayers = [];

    private $net = [];

    private $weights = [];

    private $biasWeights = [];

    private $values = [];

    private $activation;

    private $totalNumNodes;

    public function __construct(array $networkLayers)
    {
        $this->networkLayers = [];
        $startNode = 0;
        $endNode = 0;

        foreach ($networkLayers as $layer => $numNodes) {
            if ($layer > 0) {
                $startNode += $networkLayers[$layer - 1];
            }

            $endNode += $numNodes;

            $this->networkLayers[] = [
                'num_nodes' => $numNodes,
                'start_node' => $startNode,
                'end_node' => $endNode - 1,
            ];
        }

        $this->totalNumNodes = array_sum($networkLayers);

        $this->initialise();
    }

    public function test(array $inputVector)
    {
//        global $numLayers;
//        global $layerSize;
//        global $alpha;
//        global $beta;
//        global $weight;
//        global $prevWeight;
//        global $out;

        //Assign input to 0th layer
        foreach ($inputVector as $key => $value) {
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
     * Initialises the nodes outputs to zero
     * and interconnection strengths to random values
     * between -0.05 and +0.05
     */
    private function initialise()
    {
        $this->net = array();
        $this->weights = array();
        $this->biasWeights = array();
        $this->values = array();
        $this->initialiseValues();
        $this->initialiseWeights();
    }

    /**
     * Initialises the nodes outputs to zero
     */
    private function initialiseValues()
    {
        $this->values = array_fill(0, $this->totalNumNodes, 0.0);
        $this->net = array_fill(0, $this->totalNumNodes, 0.0);
    }

    /**
     * Initialises interconnection strengths to random values
     * between -0.05 and +0.05
     */
    private function initialiseWeights()
    {
        foreach ($this->networkLayers as $num => $layer) {
            if ($num < count($this->networkLayers) - 1) {
                //Calculate non bias weights
                for ($i = $layer['start_node']; $i <= $layer['end_node']; ++$i) {
                    for ($j = $this->networkLayers[$num + 1]['start_node']; $j <= $this->networkLayers[$num + 1]['end_node']; ++$j) {
                        $this->weights[$i][$j] = rand(-5, 5) / 100;
                    }
                }
                //Calculate bias weights
                for ($b = $this->networkLayers[$num + 1]['start_node']; $b <= $this->networkLayers[$num + 1]['end_node']; ++$b) {
                    $this->biasWeights[$num][$b] = rand(-5, 5) / 100;
                }
            }
        }
    }
}
