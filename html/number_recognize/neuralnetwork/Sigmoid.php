<?php

declare(strict_types=1);

namespace number_recognize\neuralnetwork;

class Sigmoid
{
    /**
     * Variable holds some information about nodes.
     * This information is defined in constructor and never changed.
     *
     * @var array
     */
    private $networkLayers = [];

    private $net = [];

    private $weights = [];

    private $biasWeights = [];

    private $values = [];

    /**
     * This variable contain total number of nodes (in all layers).
     * This information is defined in constructor and never changed.
     *
     * @var int
     */
    private $totalNumNodes;

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

    /**
     * You can easily get stuck in a local minima and the algorithm may think you reach the global minima
     * leading to sub-optimal results. To avoid this situation, we use a momentum term in the objective
     * function, which is a value between 0 and 1 that increases the size of the steps taken towards the
     * minimum by trying to jump from a local minima. If the momentum term is large then the learning rate
     * should be kept smaller.
     *
     * NOTE: empiric variable defined in constructor. Need play, to find perfect value. Value examples: 0.7, 0.3 ...
     *
     * @var float
     */
    private $momentum;

    /**
     * Minimal error level.
     * By reaching it, will stop learning.
     *
     * NOTE: empiric variable defined in constructor. Need play, to find perfect value. Value examples: 0.7, 0.3 ...
     *
     * @var float
     */
    private $minimumError;

    /**
     * Max epochs number.
     * By reaching it, will stop learning.
     *
     * NOTE: empiric variable defined in constructor. Need play, to find perfect value. Value examples: 0.7, 0.3 ...
     *
     * @var int
     */
    private $maxNumEpochs;

    /**
     * Epochs number, that was required when learning.
     *
     * @var int
     */
    private $numEpochs;

    public function __construct(
        array $networkLayers,
        float $learningRate,
        float $momentum,
        float $minimumError = 0.005,
        int $maxNumEpochs = 2000
    ) {
        $this->networkLayers = [];
        $startNode = 0;
        $endNode = 0;
        $this->learningRate = $learningRate;
        $this->momentum = $momentum;
        $this->minimumError = $minimumError;
        $this->maxNumEpochs = $maxNumEpochs;

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

    /**
     * Activate the neural network by passing
     * the values to the input layer.
     *
     * The output of each layer to feed forward
     * to the output layer
     *
     * @param array $inputVector
     *
     * @return array
     */
    public function test(array $inputVector): array
    {
        // Assign input values to input layer"
        for ($z = 0; $z < $this->networkLayers[0]['num_nodes']; ++$z) {
            $this->values[$z] = $inputVector[$z];
        }

        foreach ($this->networkLayers as $num => $layer) {
            if ($num > 0) {
                for ($j = $layer['start_node']; $j <= $layer['end_node']; ++$j) {
                    $net = 0;
                    for ($i = $this->networkLayers[$num - 1]['start_node']; $i <= $this->networkLayers[$num - 1]['end_node']; ++$i) {
                        $net += $this->values[$i] * $this->weights[$i][$j];
                    }
                    $net += $this->biasWeights[$num - 1][$j];
                    $this->net[$j] = $net;
                    $this->values[$j] = $this->sigmoid($net);
                }
            }
        }

        // Return the values from the output layer:
        $startNode = $this->networkLayers[count($this->networkLayers) - 1]['start_node'];
        $endNode = $this->networkLayers[count($this->networkLayers) - 1]['end_node'];
        return array_slice($this->values, $startNode, ($endNode + 1) - $startNode);
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
        $this->numEpochs = 1;

        do {
            if ($this->numEpochs > $this->maxNumEpochs) {
                return false;
            }

            $sumNetworkError = 0;
            foreach ($trainingSets as $trainingSet) {
                $outputs = $this->test($trainingSet);
                $this->calculateNodeDeltas($trainingSet);
                $this->calculateGradients();
                $this->calculateWeightUpdates();
                $this->applyWeightChanges();
                $sumNetworkError += $this->calculateNetworkError($trainingSet);
            }

            $globalError = $sumNetworkError / count($trainingSets);

            $this->numEpochs++;
        } while ($globalError > $this->minimumError);

        return true;
    }

    private function sigmoid($t)
    {
        return 1 / (1 + exp(-$t));
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

    /**
     * Calculate error and propagate back
     * calculating node deltas for output nodes
     * and hidden layers
     *
     * @param array $trainingSet
     */
    private function calculateNodeDeltas(array $trainingSet)
    {
        $networkLayers = $this->network->getNetworkLayers();
        $idealOutputs = array_slice($trainingSet, -1 * $networkLayers[count($networkLayers) - 1]['num_nodes']);
        $startNode = $networkLayers[count($networkLayers) - 1]['start_node'];
        $endNode = $networkLayers[count($networkLayers) - 1]['end_node'];
        $activation = $this->network->getActivation();

        //Calculate node delta for output nodes
        $j = 0;
        for ($i = $startNode; $i <= $endNode; ++$i) {
            $error = $this->network->getValue($i) - $idealOutputs[$j];
            $this->nodeDeltas[$i] = (-1 * $error) * $activation->getDerivative($this->network->getNet($i));
            ++$j;
        }
        //Calculate node delta for hidden nodes
        for ($k = count($networkLayers) - 2; $k > 0; --$k) {
            $startNode = $networkLayers[$k]['start_node'];
            $endNode = $networkLayers[$k]['end_node'];
            for ($z = $startNode; $z <= $endNode; ++$z) {
                $sum = 0;
                foreach ($this->network->getWeight($z) as $connectedNode => $weight) {
                    $sum += $weight * $this->nodeDeltas[$connectedNode];
                }
                $this->nodeDeltas[$z] = $activation->getDerivative($this->network->getNet($z)) * $sum;
            }
        }
    }

    /**
     * Calculate gradients for bias and non bias weights.
     */
    private function calculateGradients()
    {
        $networkLayers = $this->network->getNetworkLayers();
        foreach ($networkLayers as $num => $layer) {
            if ($num < count($networkLayers) - 1) {
                //Calculate gradients for non bias weights
                for ($i = $layer['start_node']; $i <= $layer['end_node']; ++$i) {
                    for ($j = $networkLayers[$num + 1]['start_node']; $j <= $networkLayers[$num + 1]['end_node']; ++$j) {
                        $this->gradients[$i][$j] = $this->network->getValue($i) * $this->nodeDeltas[$j];
                    }
                }
                //Calculate gradents for bias weights
                for ($b = $networkLayers[$num + 1]['start_node']; $b <= $networkLayers[$num + 1]['end_node']; ++$b) {
                    $this->biasGradients[$num][$b] = $this->nodeDeltas[$b];
                }
            }
        }
    }

    /**
     * Calculate weight updates using gradients and momentum
     * for bias and non bias weights
     */
    private function calculateWeightUpdates()
    {
        $networkLayers = $this->network->getNetworkLayers();
        foreach ($networkLayers as $num => $layer) {
            if ($num < count($networkLayers) - 1) {
                //Calculate weight changes for non bias weights
                for ($i = $layer['start_node']; $i <= $layer['end_node']; ++$i) {
                    for ($j = $networkLayers[$num + 1]['start_node']; $j <= $networkLayers[$num + 1]['end_node']; ++$j) {
                        $this->weightUpdates[$i][$j] = ($this->learningRate * $this->gradients[$i][$j]) + ($this->momentum * $this->weightUpdates[$i][$j]);
                    }
                }
                //Calculate weight changes for bias weights
                for ($b = $networkLayers[$num + 1]['start_node']; $b <= $networkLayers[$num + 1]['end_node']; ++$b) {
                    $this->biasWeightUpdates[$num][$b] = ($this->learningRate * $this->biasGradients[$num][$b]) + ($this->momentum * $this->biasWeightUpdates[$num][$b]);
                }
            }
        }
    }

    /**
     * Apply weight changes to neural network
     */
    private function applyWeightChanges()
    {
        $networkLayers = $this->network->getNetworkLayers();
        foreach ($networkLayers as $num => $layer) {
            if ($num < count($networkLayers) - 1) {
                //Calculate weight changes for non bias weights
                for ($i = $layer['start_node']; $i <= $layer['end_node']; ++$i) {
                    for ($j = $networkLayers[$num + 1]['start_node']; $j <= $networkLayers[$num + 1]['end_node']; ++$j) {
                        $this->network->updateWeight($i, $j, $this->weightUpdates[$i][$j]);
                    }
                }
                //Calculate weight changes for bias weights
                for ($b = $networkLayers[$num + 1]['start_node']; $b <= $networkLayers[$num + 1]['end_node']; ++$b) {
                    $this->network->updateBiasWeight($num, $b, $this->biasWeightUpdates[$num][$b]);
                }
            }
        }
    }

    /**
     * Calculate network error
     *
     * @param array $trainingSet
     *
     * @return float
     */
    private function calculateNetworkError(array $trainingSet)
    {
        $networkLayers = $this->network->getNetworkLayers();
        $idealOutputs = array_slice($trainingSet, -1 * $networkLayers[count($networkLayers) - 1]['num_nodes']);
        $startNode = $networkLayers[count($networkLayers) - 1]['start_node'];
        $endNode = $networkLayers[count($networkLayers) - 1]['end_node'];
        $numNodes = $networkLayers[count($networkLayers) - 1]['num_nodes'];
        $j = 0;
        $sum = 0;
        for ($i = $startNode; $i <= $endNode; ++$i) {
            $error = $idealOutputs[$j] - $this->network->getValue($i);
            $sum += $error * $error;
            ++$j;
        }
        $globalError = (1 / $numNodes) * $sum;
        return $globalError;
    }
}
