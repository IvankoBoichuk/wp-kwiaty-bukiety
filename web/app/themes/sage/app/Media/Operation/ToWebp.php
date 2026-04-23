<?php

namespace App\Media\Operation;

use App\Media\Image;

final class ToWebp extends AbstractOperation
{
    public function __construct(
        private readonly ?int $quality = 82,
    ) {}

    public function apply(Image $image): string
    {
        $sourcePath = $this->sourcePath($image);

        if ($sourcePath === null) {
            return $image->src();
        }

        $targetPath = $this->buildTargetPath($image, 'towebp-q'.($this->quality ?? 'default'), 'webp');
        $existing = $this->existingTargetUrl($image, $targetPath);

        if ($existing !== null) {
            return $existing;
        }

        $editor = $this->editor($image);

        if (is_wp_error($editor) || ! is_string($targetPath)) {
            return $image->src();
        }

        $this->applyQuality($editor, $this->quality);

        return $this->saveEditor($image, $editor, $targetPath, 'image/webp');
    }
}