<?php

declare(strict_types=1);

namespace number_recognize;

class MnistDataSetReaderTesting
{
    /**
     * @param string $imagePath
     *
     * @throws \Exception
     */
    public static function readImages(string $imagePath): void
    {
        $stream = fopen($imagePath, 'rb');

        try {
            // Binary-safe file read up to 16 bytes from the file pointer $stream:
            $header = fread($stream, 16);

            // Unpack data from binary string into an array according to the given format (first parameter):
            $fields = unpack('Nmagic/Nsize/Nrows/Ncols', $header);

            // Check if magic image is ok as expected:
            if ($fields['magic'] !== MnistDataSetReader::MAGIC_IMAGE) {
                throw new \Exception('Invalid magic number: ' . $imagePath);
            }

            // Looping all in file available images:
            for ($i = 0; $i < $fields['size']; $i++) {
                // Read image:
                $imageBytes = fread($stream, $fields['rows'] * $fields['cols']);

                // Converting to byte array:
                $imageBytesArray = unpack('C*', $imageBytes);

                $data = base64_decode($imageBytesArray);
                $im = imagecreatefromstring($data);
                imagepng($im, "z_{$i}.png");
                imagedestroy($im);

                // Convert to float between 0 and 1:
//                $imageFloat = array_map(function ($b) {
//                    return $b / 255;
//                }, array_values(unpack('C*', $imageBytes)));

                //print_r(unpack('C*', $imageBytes));
                return;

                //print_r($imageFloat);

                //$imageData = $imageBytes;
                //$imageData = base64_decode($imageBytes);
                //$imageData = base64_encode($imageBytes);

                //$im = imagecreatefromstring($imageData);
                $im = null;

                if ($imageBytes) {
                    echo "HASH: " . hash('md5', $imageBytes, false) . (($im === false) ? " Wrong" : " OK");
                }

                if ($im) {
                    // Save the image to file.png
                    imagepng($im, "z_{$i}.png");

                    // Destroy image:
                    imagedestroy($im);
                }

                echo "\n";

                // Interrupting:
                if ($i === 5) {
                    return;
                }
            }


            print_r($fields);
        } finally {
            fclose($stream);
        }
    }

    public static function createPngTest(): void
    {
        // Set the image (width x height) - returns an image identifier representing
        // a black image of the specified size.:
        $img = imagecreatetruecolor(28, 28);
        imagesavealpha($img, true);

        // Fill the image with transparent color
        //$color = imagecolorallocatealpha($img, 0x00, 0x00, 0x00, 127);
        //imagefill($img, 0, 0, $color);

        // Save the image to file.png
        imagepng($img, "z.png");

        // Destroy image:
        imagedestroy($img);
    }
}

//                $fp = fopen("z_{$i}.png", 'w');
//                fwrite($fp, $imageBytes);
//                fclose($fp);

//                with open(output_filename, "wb") as h:
//            w = png.Writer(cols, rows, greyscale=True)
//            data_i = [
//                data[ (i*rows*cols + j*cols) : (i*rows*cols + (j+1)*cols) ]
//                for j in range(rows)
//            ]
//            w.write(h, data_i)
/*
  $your_text = "Helloooo Worldddd";
$IMG = imagecreate( 250, 80 );
$background = imagecolorallocate($IMG, 0,0,255);
$text_color = imagecolorallocate($IMG, 255,255,0);
$line_color = imagecolorallocate($IMG, 128,255,0);
imagestring( $IMG, 10, 1, 25, $your_text,  $text_color );
imagesetthickness ( $IMG, 5 );
imageline( $IMG, 30, 45, 165, 45, $line_color );
header( "Content-type: image/png" );
imagepng($IMG);
imagecolordeallocate($IMG, $line_color );
imagecolordeallocate($IMG, $text_color );
imagecolordeallocate($IMG, $background );
imagedestroy($IMG);
exit;

In octave, load the image using imread() function, then transform the image using im2double() function.

<?php
$array = array();
foreach(str_split(file_get_contents('image.jpg')) as $byte){
  array_push($array, ord($byte));
}
 */
