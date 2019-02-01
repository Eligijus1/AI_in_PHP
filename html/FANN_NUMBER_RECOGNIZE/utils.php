<?php
/**
 * Print a message and break line
 *
 * @param string $message
 */
function println(string $message)
{
    echo $message . PHP_EOL;
}

/**
 * Print a message using println and exit
 *
 * @param string $message
 * @param int    $status
 */
function quit(string $message, int $status = 1)
{
    println($message);
    exit($status);
}

/**
 * Return the index of the highest value in an array
 *
 * @param array $output
 *
 * @return int
 */
function argmax(array $output): int
{
    return array_keys($output, max($output))[0];
}

/**
 * Return the highest value in an array
 *
 * @param array $output
 *
 * @return int
 */
function amax(array $output): int
{
    return max($output);
}

/**
 * Read an int from a file handler and unpack it
 *
 * @param $f
 *
 * @return mixed
 */
function freadint($f)
{
    return unpack("N", fread($f, 4))[1];
}

/**
 * Read a char from a file handler and unpack it
 *
 * @param $f
 *
 * @return mixed
 */
function freadchar($f)
{
    return unpack("C", fread($f, 1))[1];
}
