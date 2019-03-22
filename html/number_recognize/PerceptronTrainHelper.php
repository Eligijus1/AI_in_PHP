<?php

declare(strict_types=1);

namespace number_recognize;

class PerceptronTrainHelper
{
    private const MAGIC_IMAGE = 0x00000803;
    private const MAGIC_LABEL = 0x00000801;

    public function train(string $imagePath, string $labelsPath): void
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        echo date_format(new \DateTime(), 'Y.m.d H:i:s') . ' INFO: Begin training with perceptron.' . PHP_EOL;

        // Do some checks:
        if (!file_exists($imagePath)) {
            echo date_format(new \DateTime(),
                    'Y.m.d H:i:s') . " ERROR: Images file {$imagePath} not exist." . PHP_EOL;
            return;
        }
        if (!file_exists($imagePath)) {
            echo date_format(new \DateTime(),
                    'Y.m.d H:i:s') . " ERROR: Labels file {$labelsPath} not exist." . PHP_EOL;
            return;
        }

        // Call method, responsible to train:
        $i = $this->trainPerceptron($imagePath, $labelsPath, 0);

        // Information about results:
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Checked {$i} numbers." . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)) . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: peak of memory allocated by PHP: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)) . PHP_EOL;
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds) . PHP_EOL;
    }

    private function trainPerceptron(string $imagePath, string $labelsPath, int $number): int
    {


        return 0;
    }
}
