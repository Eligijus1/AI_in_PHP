<?php

declare(strict_types=1);

namespace number_recognize\neuralnetwork;

use number_recognize\helpers\HelperFunctions;

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

    /**
     * Calculated deltas of weights.
     *
     * error = actual - expected
     * weight_delta = error * sigmoid(x)dx
     *
     * sigmoid(x)dx - sigmoid derivative function.
     *
     * NOTE: sigmoid(x)dx = sigmoid(x)dx * (1 - sigmoid(x)dx)
     *
     * @var array
     */
    private $nodeDeltas = [];

    /**
     * Calculated gradients.
     *
     * @var array
     */
    private $gradients = [];

    /**
     * @var array
     */
    private $biasGradients = [];

    /**
     * @var array
     */
    private $biasWeightUpdates = [];

    /**
     * @var array
     */
    private $weightUpdates = [];

    /**
     * @var float|null
     */
    private $latestTrainGlobalError = null;

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
    public function activate(array $inputVector): array
    {
        // Assign input values to input layer:
        for ($z = 0; $z < $this->networkLayers[0]['num_nodes']; ++$z) {
            $this->values[$z] = $inputVector[$z];
        }

        // Here $this->values contain only input data.

        // Other layers values:
        foreach ($this->networkLayers as $num => $layer) {
            if ($num > 0) {
                for ($j = $layer['start_node']; $j <= $layer['end_node']; ++$j) {
                    $net = 0;
                    for ($i = $this->networkLayers[$num - 1]['start_node']; $i <= $this->networkLayers[$num - 1]['end_node']; ++$i) {

                        // Check for errors:
                        if (!isset($this->values[$i])) {
                            HelperFunctions::printError("\$this->values[{$i}] in activate method not defined.");
                            exit(0);
                        }
                        if (!isset($this->weights[$i][$j])) {
                            HelperFunctions::printError("\$this->weights[{$i}][{$j}] int activate method not defined.");
                            exit(0);
                        }

                        // Multiplication of next layers values and weights:
                        $net += $this->values[$i] * $this->weights[$i][$j];
                    }
                    $net += $this->biasWeights[$num - 1][$j];
                    $this->net[$j] = $net;
                    $this->values[$j] = $this->getActivation($net);
                }
            }
        }

        // Here $this->values contain all data.

        return $this->getOutputs();
    }

    /**
     * Training network on the provided
     * training set.
     *
     * @param array $trainingSets
     * @param int   $maxNumEpochs
     *
     * @return float
     */
    public function train(array $trainingSets, int $maxNumEpochs = null): ?float
    {
        $globalError = null;
        $this->numEpochs = 1;

        // Define maximum epochs number:
        if ($maxNumEpochs) {
            $this->maxNumEpochs = $this->maxNumEpochs + $maxNumEpochs;
        } else {
            $maxNumEpochs = $this->maxNumEpochs;
        }

        // Loop until global error will reach minimum error:
        do {
            // If epoch number reached max requested epochs number, simple interrupt:
            if ($this->numEpochs > $maxNumEpochs) {
                return $globalError;
            }

            // Loop every training data set:
            $sumNetworkError = 0;
            foreach ($trainingSets as $key => $trainingSet) {
                // First try activate existing network with current input data:
                $this->activate($trainingSet);

                // Calculating node deltas:
                $this->calculateNodeDeltas($trainingSet);

                $this->calculateGradients();

                $this->calculateWeightUpdates();

                $this->applyWeightChanges();

                $sumNetworkError += $this->calculateNetworkError($trainingSet);

                // Output some information:
                //HelperFunctions::printInfo("Finished training with training set {$key}, epoch {$this->numEpochs} of {$this->maxNumEpochs}.");
            }

            $globalError = $sumNetworkError / count($trainingSets);

            $this->numEpochs++;
        } while ($globalError > $this->minimumError);

        // Finish and return error level:
        $this->latestTrainGlobalError = $globalError;
        return $globalError;
    }

    /**
     * Gets the values from the output layer
     *
     * @return array
     */
    public function getOutputs(): array
    {
        $startNode = $this->networkLayers[count($this->networkLayers) - 1]['start_node'];
        $endNode = $this->networkLayers[count($this->networkLayers) - 1]['end_node'];
        return array_slice($this->values, $startNode, ($endNode + 1) - $startNode);
    }

    /**
     * Calculate and return sigmoid value.
     *
     * @param float $net
     *
     * @return float
     */
    public function getActivation(float $net): float
    {
        return 1 / (1 + exp(-$net));
    }

    /**
     * Calculate and return sigmoid derivative value.
     *
     * @param float $net
     *
     * @return float
     */
    public function getDerivative(float $net): float
    {
        return $this->getActivation($net) * (1 - $this->getActivation($net));
    }

    /**
     * Initialises the nodes.
     */
    private function initialise()
    {
        $this->net = [];
        $this->weights = [];
        $this->biasWeights = [];
        $this->values = [];
        $this->nodeDeltas = array_fill(0, $this->totalNumNodes, 0.0);
        $this->values = array_fill(0, $this->totalNumNodes, 0.0);
        $this->net = array_fill(0, $this->totalNumNodes, 0.0);
        $this->gradients = [];
        $this->biasGradients = [];
        $this->biasWeightUpdates = [];
        $this->weightUpdates = [];
        $this->initialiseWeights();
    }

    /**
     * Initialise weight updates to zero
     */
    protected function initialiseWeights()
    {
        foreach ($this->networkLayers as $num => $layer) {
            if ($num < count($this->networkLayers) - 1) {
                //Calculate non bias weights:
                for ($i = $layer['start_node']; $i <= $layer['end_node']; ++$i) {
                    for ($j = $this->networkLayers[$num + 1]['start_node']; $j <= $this->networkLayers[$num + 1]['end_node']; ++$j) {
                        $this->weights[$i][$j] = rand(-5, 5) / 100;
                        $this->weightUpdates[$i][$j] = 0.0;
                    }
                }

                //Calculate bias weights:
                for ($b = $this->networkLayers[$num + 1]['start_node']; $b <= $this->networkLayers[$num + 1]['end_node']; ++$b) {
                    //$this->biasWeights[$num][$b] = rand(-5, 5) / 100;
                    $this->biasWeights[$num][$b] = 0.0;
                    $this->biasWeightUpdates[$num][$b] = 0.0;
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
        $idealOutputs = array_slice($trainingSet,
            -1 * $this->networkLayers[count($this->networkLayers) - 1]['num_nodes']);
        $startNode = $this->networkLayers[count($this->networkLayers) - 1]['start_node'];
        $endNode = $this->networkLayers[count($this->networkLayers) - 1]['end_node'];

        //Calculate node delta for output nodes
        $j = 0;
        for ($i = $startNode; $i <= $endNode; ++$i) {
            $error = $this->values[$i] - $idealOutputs[$j];
            $this->nodeDeltas[$i] = (-1 * $error) * $this->getDerivative($this->net[$i]);
            ++$j;
        }

        //Calculate node delta for hidden nodes
        for ($k = count($this->networkLayers) - 2; $k > 0; --$k) {
            $startNode = $this->networkLayers[$k]['start_node'];
            $endNode = $this->networkLayers[$k]['end_node'];
            for ($z = $startNode; $z <= $endNode; ++$z) {
                $sum = 0;
                foreach ($this->weights[$z] as $connectedNode => $weight) {
                    $sum += $weight * $this->nodeDeltas[$connectedNode];
                }
                $this->nodeDeltas[$z] = $this->getDerivative($this->net[$z]) * $sum;
            }
        }
    }

    /**
     * Calculate gradients for bias and non bias weights.
     */
    private function calculateGradients()
    {
        foreach ($this->networkLayers as $num => $layer) {
            if ($num < count($this->networkLayers) - 1) {
                //Calculate gradients for non bias weights
                for ($i = $layer['start_node']; $i <= $layer['end_node']; ++$i) {
                    for ($j = $this->networkLayers[$num + 1]['start_node']; $j <= $this->networkLayers[$num + 1]['end_node']; ++$j) {
                        $this->gradients[$i][$j] = $this->values[$i] * $this->nodeDeltas[$j];
                    }
                }

                //Calculate gradients for bias weights
                for ($b = $this->networkLayers[$num + 1]['start_node']; $b <= $this->networkLayers[$num + 1]['end_node']; ++$b) {
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
        foreach ($this->networkLayers as $num => $layer) {
            if ($num < count($this->networkLayers) - 1) {
                //Calculate weight changes for non bias weights:
                for ($i = $layer['start_node']; $i <= $layer['end_node']; ++$i) {
                    for ($j = $this->networkLayers[$num + 1]['start_node']; $j <= $this->networkLayers[$num + 1]['end_node']; ++$j) {
                        $this->weightUpdates[$i][$j] = ($this->learningRate * $this->gradients[$i][$j]) + ($this->momentum * $this->weightUpdates[$i][$j]);
                    }
                }

                //Calculate weight changes for bias weights:
                for ($b = $this->networkLayers[$num + 1]['start_node']; $b <= $this->networkLayers[$num + 1]['end_node']; ++$b) {
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
        foreach ($this->networkLayers as $num => $layer) {
            if ($num < count($this->networkLayers) - 1) {
                //Calculate weight changes for non bias weights
                for ($i = $layer['start_node']; $i <= $layer['end_node']; ++$i) {
                    for ($j = $this->networkLayers[$num + 1]['start_node']; $j <= $this->networkLayers[$num + 1]['end_node']; ++$j) {
                        $this->updateWeight($i, $j, $this->weightUpdates[$i][$j]);
                    }
                }
                //Calculate weight changes for bias weights
                for ($b = $this->networkLayers[$num + 1]['start_node']; $b <= $this->networkLayers[$num + 1]['end_node']; ++$b) {
                    $this->updateBiasWeight($num, $b, $this->biasWeightUpdates[$num][$b]);
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
        $idealOutputs = array_slice($trainingSet,
            -1 * $this->networkLayers[count($this->networkLayers) - 1]['num_nodes']);
        $startNode = $this->networkLayers[count($this->networkLayers) - 1]['start_node'];
        $endNode = $this->networkLayers[count($this->networkLayers) - 1]['end_node'];
        $numNodes = $this->networkLayers[count($this->networkLayers) - 1]['num_nodes'];
        $j = 0;
        $sum = 0;
        for ($i = $startNode; $i <= $endNode; ++$i) {
            $error = $idealOutputs[$j] - $this->values[$i];
            $sum += $error * $error;
            ++$j;
        }
        $globalError = (1 / $numNodes) * $sum;
        return $globalError;
    }

    /**
     * Updates the weight between node $i
     * and $j with given weight value
     *
     * @param int   $i
     * @param int   $j
     * @param float $weight
     */
    public function updateWeight($i, $j, $weight)
    {
        $this->weights[$i][$j] += $weight;
    }

    /**
     * Updates the bias weight between node $i
     * and $j with given weight value
     *
     * @param int   $i
     * @param int   $j
     * @param float $weight
     */
    public function updateBiasWeight($i, $j, $weight)
    {
        $this->biasWeights[$i][$j] += $weight;
    }

    public function getLearningRate(): float
    {
        return $this->learningRate;
    }

    public function getMomentum(): float
    {
        return $this->momentum;
    }

    public function getMaxNumEpochs(): int
    {
        return $this->maxNumEpochs;
    }

    public function getLatestTrainGlobalError(): ?float
    {
        return $this->latestTrainGlobalError;
    }
}
