<?php

namespace Nishadil\PlaceholderImg;

use Nishadil\PlaceholderImg\Exceptions\InvalidDimensionsException;

class PlaceholderImg
{
    private $width;
    private $height;
    private $backgroundColor;
    private $gradientColor;
    private $text;
    private $textColor;
    private $fontFamily;
    private $shape;
    private $outputFormat;
    private $textSize;
    private $foregroundColor;
    private $textAlign;
    private $textShadowColor;
    private $textStrokeColor;
    private $textStrokeWidth;
    private $gradientDirection;
    private $backgroundPattern;
    private $backgroundPatternColor;
    private $backgroundRandomPattern;
    private $outputQuality;

    // Constants for default values
    const DEFAULT_GRADIENT_DIRECTION = 'to right';
    const DEFAULT_BACKGROUND_PATTERN = '';
    const DEFAULT_BACKGROUND_PATTERN_COLOR = 'ffffff';
    const DEFAULT_BACKGROUND_RANDOM_PATTERN = false;
    const DEFAULT_OUTPUT_QUALITY = 100;

    public function __construct(
        $width,
        $height,
        $backgroundColor,
        $foregroundColor,
        $text,
        $textColor,
        $fontFamily,
        $shape,
        $outputFormat,
        $textSize,
        $gradientColor,
        $textAlign,
        $textShadowColor = '',
        $textStrokeColor = '',
        $textStrokeWidth = 0,
        $gradientDirection = self::DEFAULT_GRADIENT_DIRECTION,
        $backgroundPattern = self::DEFAULT_BACKGROUND_PATTERN,
        $backgroundPatternColor = self::DEFAULT_BACKGROUND_PATTERN_COLOR,
        $backgroundRandomPattern = self::DEFAULT_BACKGROUND_RANDOM_PATTERN,
        $outputQuality = self::DEFAULT_OUTPUT_QUALITY
    ) {
        $this->width = $width;
        $this->height = $height;
        $this->backgroundColor = $this->sanitizeColorCode($backgroundColor);
        $this->gradientColor = $this->sanitizeColorCode($gradientColor);
        $this->text = $text;
        $this->textColor = $this->sanitizeColorCode($textColor);
        $this->fontFamily = $fontFamily;
        $this->shape = $shape;
        $this->outputFormat = $outputFormat;
        $this->textSize = $textSize;
        $this->foregroundColor = $this->sanitizeColorCode($foregroundColor);
        $this->textAlign = $textAlign;
        $this->textShadowColor = $this->sanitizeColorCode($textShadowColor);
        $this->textStrokeColor = $this->sanitizeColorCode($textStrokeColor);
        $this->textStrokeWidth = $textStrokeWidth;
        $this->gradientDirection = $gradientDirection;
        $this->backgroundPattern = $backgroundPattern;
        $this->backgroundPatternColor = $this->sanitizeColorCode($backgroundPatternColor);
        $this->backgroundRandomPattern = $backgroundRandomPattern;
        $this->outputQuality = $this->sanitizeQualityValue($outputQuality);
    }

    public function generateImageUrl()
    {
        $image = $this->createImage();
        $this->applyBackground($image);
        $this->applyText($image);
        $this->applyGradientBackground($image);
        $this->applyRandomBackgroundPattern($image);
        $this->applyTextShadow($image);
        $this->applyTextStroke($image);
        $this->outputImage($image);

        return $this->outputFormat;
    }

    public function resizeAndReduceQuality($newWidth, $newHeight, $quality)
    {
        $this->validateDimensions($newWidth, $newHeight);

        $resizedImage = $this->createImage($newWidth, $newHeight);

        $this->applyBackground($resizedImage);
        $this->applyText($resizedImage);
        $this->applyGradientBackground($resizedImage);
        $this->applyRandomBackgroundPattern($resizedImage);
        $this->applyTextShadow($resizedImage);
        $this->applyTextStroke($resizedImage);
        $this->outputImage($resizedImage, $quality);

        return $this->outputFormat;
    }

    private function sanitizeColorCode($color)
    {
        $color = ltrim($color, '#');
        return preg_replace('/[^a-fA-F0-9]/', '', $color);
    }

    private function sanitizeQualityValue($quality)
    {
        return max(1, min(100, (int)$quality));
    }

    private function validateDimensions($width, $height)
    {
        if (!is_numeric($width) || !is_numeric($height) || $width <= 0 || $height <= 0) {
            throw new InvalidDimensionsException('Invalid dimensions. Width and height must be numeric values greater than zero.');
        }
    }

    private function outputImage($image, $quality = null)
    {
        $outputQuality = ($quality !== null) ? $this->sanitizeQualityValue($quality) : $this->outputQuality;

        switch ($this->outputFormat) {
            case 'jpg':
                header('Content-Type: image/jpeg');
                imagejpeg($image, null, $outputQuality);
                break;
            case 'png':
                header('Content-Type: image/png');
                imagepng($image);
                break;
            case 'webp':
                header('Content-Type: image/webp');
                imagewebp($image, null, $outputQuality);
                break;
            default:
                throw new \RuntimeException('Invalid output format specified.');
        }

        imagedestroy($image);
        exit;
    }

    private function createImage($width = null, $height = null)
    {
        $imageWidth = $width ?? $this->width;
        $imageHeight = $height ?? $this->height;

        $image = imagecreatetruecolor($imageWidth, $imageHeight);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    private function applyBackground($image)
    {
        $backgroundColor = imagecolorallocate($image, hexdec(substr($this->backgroundColor, 0, 2)), hexdec(substr($this->backgroundColor, 2, 2)), hexdec(substr($this->backgroundColor, 4, 2)));
        imagefill($image, 0, 0, $backgroundColor);
    }

    private function applyText($image)
    {
        $textColor = imagecolorallocate($image, hexdec(substr($this->textColor, 0, 2)), hexdec(substr($this->textColor, 2, 2)), hexdec(substr($this->textColor, 4, 2)));

        $fontFile = __DIR__ . "/../fonts/{$this->fontFamily}.ttf";

        $xPosition = $this->getWidthCenterPosition($image, $this->textSize, $this->text);
        $yPosition = $this->getTextYPosition();

        imagettftext($image, $this->textSize, 0, $xPosition, $yPosition, $textColor, $fontFile, $this->text);
    }

    private function applyTextShadow($image)
    {
        if (!empty($this->textShadowColor)) {
            $textShadowColor = imagecolorallocate($image, hexdec(substr($this->textShadowColor, 0, 2)), hexdec(substr($this->textShadowColor, 2, 2)), hexdec(substr($this->textShadowColor, 4, 2)));

            $xPosition = $this->getWidthCenterPosition($image, $this->textSize, $this->text);
            $yPosition = $this->getTextYPosition();

            imagettftext($image, $this->textSize, 0, $xPosition + 2, $yPosition + 2, $textShadowColor, __DIR__ . "/../fonts/{$this->fontFamily}.ttf", $this->text);
        }
    }

    private function applyTextStroke($image)
    {
        if (!empty($this->textStrokeColor) && $this->textStrokeWidth > 0) {
            $textStrokeColor = imagecolorallocate($image, hexdec(substr($this->textStrokeColor, 0, 2)), hexdec(substr($this->textStrokeColor, 2, 2)), hexdec(substr($this->textStrokeColor, 4, 2)));

            $xPosition = $this->getWidthCenterPosition($image, $this->textSize, $this->text);
            $yPosition = $this->getTextYPosition();

            for ($i = 1; $i <= $this->textStrokeWidth; $i++) {
                imagettftext($image, $this->textSize, 0, $xPosition - $i, $yPosition, $textStrokeColor, __DIR__ . "/../fonts/{$this->fontFamily}.ttf", $this->text);
                imagettftext($image, $this->textSize, 0, $xPosition + $i, $yPosition, $textStrokeColor, __DIR__ . "/../fonts/{$this->fontFamily}.ttf", $this->text);
                imagettftext($image, $this->textSize, 0, $xPosition, $yPosition - $i, $textStrokeColor, __DIR__ . "/../fonts/{$this->fontFamily}.ttf", $this->text);
                imagettftext($image, $this->textSize, 0, $xPosition, $yPosition + $i, $textStrokeColor, __DIR__ . "/../fonts/{$this->fontFamily}.ttf", $this->text);
            }
        }
    }

    private function applyGradientBackground($image)
    {
        if (!empty($this->gradientColor) && $this->shape === 'rectangle') {
            $gradientStartColor = imagecolorallocate($image, hexdec(substr($this->gradientColor, 0, 2)), hexdec(substr($this->gradientColor, 2, 2)), hexdec(substr($this->gradientColor, 4, 2)));

            $width = imagesx($image);
            $height = imagesy($image);

            for ($i = 0; $i < $height; $i++) {
                $r = ($i / $height) * 255;
                $g = ($i / $height) * 255;
                $b = ($i / $height) * 255;

                $gradientColor = imagecolorallocate($image, $r, $g, $b);

                imageline($image, 0, $i, $width, $i, $gradientColor);
            }
        }
    }

    private function applyRandomBackgroundPattern($image)
    {
        if ($this->backgroundRandomPattern) {
            $patternFiles = glob(__DIR__ . '/../patterns/*.png');
            if (!empty($patternFiles)) {
                $randomPatternFile = $patternFiles[array_rand($patternFiles)];
                $patternImage = imagecreatefrompng($randomPatternFile);
                imagealphablending($patternImage, true);
                imagesavealpha($patternImage, true);
                imagecopyresampled($image, $patternImage, 0, 0, 0, 0, $this->width, $this->height, imagesx($patternImage), imagesy($patternImage));
                imagedestroy($patternImage);
            }
        }
    }

    private function getTextYPosition()
    {
        $fontSize = $this->textSize;
        $textHeight = imagefontheight($fontSize);

        switch ($this->textAlign) {
            case 'top':
                return $textHeight;
            case 'center':
                return $this->height / 2 + $textHeight / 2;
            case 'bottom':
                return $this->height - $textHeight / 2;
            default:
                return $this->height / 2;
        }
    }

    private function getWidthCenterPosition($image, $fontSize, $text)
    {
        $textWidth = imagettfbbox($fontSize, 0, __DIR__ . "/../fonts/{$this->fontFamily}.ttf", $text);
        return ($this->width - $textWidth[4]) / 2;
    }
}
