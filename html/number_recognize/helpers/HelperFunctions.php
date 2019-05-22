<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use DateTime;
use Exception;
use number_recognize\MnistDataSetReader;

class HelperFunctions
{
    public const MAGIC_IMAGE = 0x00000803;
    public const MAGIC_LABEL = 0x00000801;

    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function formatMilliseconds($milliseconds)
    {
        $seconds = floor($milliseconds / 1000);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $milliseconds = $milliseconds % 1000;
        $seconds = $seconds % 60;
        $minutes = $minutes % 60;

        $format = '%u:%02u:%02u.%03u';
        $time = sprintf($format, $hours, $minutes, $seconds, $milliseconds);
        return rtrim($time, '0');
    }

    /**
     * Read MNIST label file.
     *
     * Format: http://yann.lecun.com/exdb/mnist/
     *
     * @param string $labelPath
     *
     * @return array
     * @throws Exception
     */
    public static function readLabels(string $labelPath): array
    {
        $stream = fopen($labelPath, 'rb');
        if (false === $stream) {
            throw new Exception('Could not open file: ' . $labelPath);
        }
        $labels = [];
        try {
            $header = fread($stream, 8);
            $fields = unpack('Nmagic/Nsize', $header);
            if ($fields['magic'] !== self::MAGIC_LABEL) {
                throw new Exception('Invalid magic number: ' . $labelPath);
            }
            $labels = fread($stream, $fields['size']);
        } finally {
            fclose($stream);
        }
        return array_values(unpack('C*', $labels));
    }

    /**
     * Simple method to print messages.
     *
     * @param string $type
     * @param string $message
     *
     * @throws Exception
     */
    public static function printMessage(string $type, string $message): void
    {
        echo date_format(new DateTime(), 'Y.m.d H:i:s') . " {$type}: {$message}" . PHP_EOL;
    }

    public static function printInfo(string $message): void
    {
        HelperFunctions::printMessage('INFO', $message);
    }

    public static function printError(string $message): void
    {
        HelperFunctions::printMessage('ERROR', $message);
    }

    public static function readImagesData(string $imagePath): array
    {
        // Open images path:
        $streamImages = fopen($imagePath, 'rb');
        $images = [];

        try {
            // Binary-safe file read up to 16 bytes from the file pointer $stream:
            $header = fread($streamImages, 16);

            // Unpack data from binary string into an array according to the given format (first parameter):
            $fields = unpack('Nmagic/Nsize/Nrows/Ncols', $header);

            // Check if magic image is ok as expected:
            if ($fields['magic'] !== MnistDataSetReader::MAGIC_IMAGE) {
                throw new Exception('Invalid magic number: ' . $imagePath);
            }

            // Looping all in file available images:
            for ($i = 0; $i < $fields['size']; $i++) {
                // Read image:
                $imageBytes = fread($streamImages, $fields['rows'] * $fields['cols']);

                // Converting to byte array:
                $imageBytesArray = unpack('C*', $imageBytes);

                $images[] = $imageBytesArray;
            }
        } finally {
            fclose($streamImages);
        }

        return $images;
    }

    /**
     * Reading images float array between 0 and 1.
     * In DB images are stored with values between 0 and 255.
     * 0 mean - nothing.
     *
     * @param string $imagePath
     *
     * @return float[]
     * @throws Exception
     */
    public static function readImagesDataAsFloatBetween0And1(string $imagePath): array
    {
        // Open images path:
        $streamImages = fopen($imagePath, 'rb');
        $images = [];

        try {
            // Binary-safe file read up to 16 bytes from the file pointer $stream:
            $header = fread($streamImages, 16);

            // Unpack data from binary string into an array according to the given format (first parameter):
            $fields = unpack('Nmagic/Nsize/Nrows/Ncols', $header);

            // Check if magic image is ok as expected:
            if ($fields['magic'] !== MnistDataSetReader::MAGIC_IMAGE) {
                throw new Exception('Invalid magic number: ' . $imagePath);
            }

            // Looping all in file available images:
            for ($i = 0; $i < $fields['size']; $i++) {
                // Read image:
                $imageBytes = fread($streamImages, $fields['rows'] * $fields['cols']);

                // Converting to byte array:
                $imageBytesArray = unpack('C*', $imageBytes);

                $images[] = array_map(function ($b) {
                    return $b / 255;
                }, array_values($imageBytesArray));
            }
        } finally {
            fclose($streamImages);
        }

        return $images;
    }
}
