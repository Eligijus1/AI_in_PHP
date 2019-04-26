<?php

declare(strict_types=1);

namespace number_recognize;

class Sigmoid
{
    public function test()
    {

    }

    public function train()
    {

    }

    function sigmoid($t)
    {
        //return 1 / (1 + pow(M_EULER, -$t));// M_EULER	0.57721566490153286061	Euler constant
        return 1 / (1 + pow(M_E, -$t));// M_E	2.7182818284590452354	e
        //return 1 / (1 + exp(-$t));
    }
}
