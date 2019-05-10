<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use DateTime;
use number_recognize\neuralnetwork\Sigmoid;

class SigmoidTrainHelper
{
    private const DATA_LOCATION = "data/train_sigmoid";

    public function train(string $imagePath, string $labelsPath): void
    {
        // Define application start time:
        $milliseconds = round(microtime(true) * 1000);

        // Print message, that starting loading:
        echo date_format(new DateTime(), 'Y.m.d H:i:s') . ' INFO: Begin training with perceptron.' . PHP_EOL;

        // Do some checks:
        if (!file_exists($imagePath)) {
            echo date_format(new DateTime(),
                    'Y.m.d H:i:s') . " ERROR: Images file {$imagePath} not exist." . PHP_EOL;
            return;
        }
        if (!file_exists($imagePath)) {
            echo date_format(new DateTime(),
                    'Y.m.d H:i:s') . " ERROR: Labels file {$labelsPath} not exist." . PHP_EOL;
            return;
        }

        // Training network:
        $sigmoid = new Sigmoid([
            784,
            15,
            10
        ]);// 1 parameter - input pixels amount; 15 - hidden layer - adjust; last - output
        //TODO: $sigmoid->train()

        // Create work if not exist:
        if (!file_exists(self::DATA_LOCATION)) {
            mkdir(self::DATA_LOCATION, 0777, true);
        }

        // Save object to disc:
        $s = serialize($sigmoid);
        file_put_contents(self::DATA_LOCATION . "/sigmoid.dat", $s);

        // Information about results:
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Memory used: " . HelperFunctions::formatBytes(memory_get_usage(true)) . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: peak of memory allocated by PHP: " . HelperFunctions::formatBytes(memory_get_peak_usage(true)) . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Done training in " . HelperFunctions::formatMilliseconds(round(microtime(true) * 1000) - $milliseconds) . PHP_EOL;
        echo date_format(new DateTime(),
                'Y.m.d H:i:s') . " INFO: Data location: " . self::DATA_LOCATION . PHP_EOL;
    }
}
