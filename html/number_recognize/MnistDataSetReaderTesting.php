<?php

declare(strict_types=1);

namespace number_recognize;

class MnistDataSetReaderTesting
{
    public static function readImages(string $imagePath): void
    {
        $stream = fopen($imagePath, 'rb');

        try {
            // Binary-safe file read up to 16 bytes from the file pointer $stream:
            $header = fread($stream, 16);

            // Unpack data from binary string into an array according to the given format (first parameter):
            $fields = unpack('Nmagic/Nsize/Nrows/Ncols', $header);

            print_r($fields);
        } finally {
            fclose($stream);
        }
    }
}
