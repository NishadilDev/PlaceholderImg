<?php

require_once __DIR__ . '/../src/PlaceholderImg.php';
require_once __DIR__ . '/../src/Exceptions/InvalidDimensionsException.php';

use Nishadil\PlaceholderImg\PlaceholderImg;
use Nishadil\PlaceholderImg\Exceptions\InvalidDimensionsException;

// Example usage:
try {
    $imageGenerator = new PlaceholderImg(
        500,
        300,
        'dddddd',
        '333333',
        'Sample Text',
        'aaaaaa',
        'arial',
        'circle',
        'png',
        30,
        'ff0000',
        'center',
        '000000', // Text shadow color
        '0000ff', // Text stroke color
        2, // Text stroke width
        'to right', // Gradient direction
        'patterns/diagonal_stripes.png', // Background pattern
        'ffffff', // Background pattern color
        true, // Random background pattern
        80 // Output quality
    );

    $imagePath = 'generated_image.' . $imageGenerator->generateImageUrl();

    echo '<h2>Original Image</h2>';
    echo '<img src="' . $imagePath . '" alt="Original Image">';

    // Resize and reduce quality
    $newWidth = 200;
    $newHeight = 120;
    $quality = 50;

    $resizedImageUrl = 'resized_image.' . $imageGenerator->resizeAndReduceQuality($newWidth, $newHeight, $quality);

    echo '<h2>Resized and Reduced Quality Image</h2>';
    echo '<img src="' . $resizedImageUrl . '" alt="Resized and Reduced Quality Image">';
} catch (InvalidDimensionsException $e) {
    echo 'Error: ' . $e->getMessage();
} catch (\InvalidArgumentException $e) {
    echo 'Error: ' . $e->getMessage();
} catch (\RuntimeException $e) {
    echo 'Error: ' . $e->getMessage();
}
