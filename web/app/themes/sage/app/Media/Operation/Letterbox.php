<?php

namespace App\Media\Operation;

use App\Media\Image;

final class Letterbox extends AbstractOperation
{
    public function __construct(
        private readonly int $width,
        private readonly int $height,
        private readonly string $background = 'ffffff',
        private readonly int $quality = 82,
    ) {}

    public function apply(Image $image): string
    {
        $sourcePath = $this->sourcePath($image);

        if ($sourcePath === null || $this->width <= 0 || $this->height <= 0) {
            return $image->src();
        }

        $extension = strtolower($image->extension());

        if ($extension === 'svg') {
            return $image->src();
        }

        $targetPath = $this->buildTargetPath(
            $image,
            sprintf('letterbox-%dx%d-%s-q%d', $this->width, $this->height, strtolower($this->background), $this->quality),
            $extension
        );
        $existing = $this->existingTargetUrl($image, $targetPath);

        if ($existing !== null) {
            return $existing;
        }

        $source = $this->openRaster($sourcePath);

        if (! $source || ! is_string($targetPath)) {
            return $image->src();
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($source);

            return $image->src();
        }

        $scale = min($this->width / $sourceWidth, $this->height / $sourceHeight);
        $scaledWidth = max(1, (int) round($sourceWidth * $scale));
        $scaledHeight = max(1, (int) round($sourceHeight * $scale));
        $offsetX = (int) floor(($this->width - $scaledWidth) / 2);
        $offsetY = (int) floor(($this->height - $scaledHeight) / 2);

        $canvas = imagecreatetruecolor($this->width, $this->height);

        if (! $canvas) {
            imagedestroy($source);

            return $image->src();
        }

        [$red, $green, $blue] = $this->hexToRgb($this->background);
        $background = imagecolorallocate($canvas, $red, $green, $blue);

        imagefill($canvas, 0, 0, $background);
        imagecopyresampled($canvas, $source, $offsetX, $offsetY, 0, 0, $scaledWidth, $scaledHeight, $sourceWidth, $sourceHeight);

        $saved = $this->saveRaster($canvas, $targetPath, $extension, $this->quality);

        imagedestroy($canvas);
        imagedestroy($source);

        return $saved ? $this->targetUrl($image, $targetPath) : $image->src();
    }
}