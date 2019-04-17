<?php

declare(strict_types=1);

namespace number_recognize\helpers;

use Exception;

class HelperFunctions
{
    public const MAGIC_IMAGE = 0x00000803;
    public const MAGIC_LABEL = 0x00000801;

    public static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function formatMilliseconds($milliseconds) {
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
}
