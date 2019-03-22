<?php

declare(strict_types=1);

namespace number_recognize;

class PerceptronTrainHelper
{
    public function train(): void
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        echo date_format(new \DateTime(), 'Y.m.d H:i:s') . ' INFO: Begin training with perceptron.' . PHP_EOL;

        // Information about results:
        echo date_format(new \DateTime(),
                'Y.m.d H:i:s') . " INFO: Done training in " . (round(microtime(true) * 1000) - $milliseconds) . " milliseconds\n";
    }
}
