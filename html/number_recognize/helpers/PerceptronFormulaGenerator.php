<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use DateTime;
use number_recognize\Perceptron;

class PerceptronFormulaGenerator
{
    private const DATA_LOCATION = "data/train_perceptron";

    public function __invoke(int $number): void
    {
        // Define file name, where will be saved data:
        $fileName = self::DATA_LOCATION . "/generated_formula/function_" . $number . "_" . date_format(new DateTime(),
                'Y-m-d_H-i-s') . ".txt";

        $perceptron = $this->getPerceptron($number);


        echo date_format(new DateTime(), 'Y.m.d H:i:s') . " INFO: Save function to file {$fileName}." . PHP_EOL;

        file_put_contents($fileName, $perceptron->getFormula(), FILE_APPEND | LOCK_EX);
    }

    private function getPerceptron(int $number): Perceptron
    {
        $data = file_get_contents(self::DATA_LOCATION . "/perceptron_for_number_{$number}.dat");

        $perceptron = unserialize($data);

        return $perceptron;
    }
}
