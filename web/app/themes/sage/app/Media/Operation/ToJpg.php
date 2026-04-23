<?php

namespace App\Media\Operation;

use App\Media\Image;

final class ToJpg extends AbstractOperation
{
    public function __construct(
        private readonly ?int $quality = 90,
    ) {}

    public function apply(Image $image): string
    {
        $sourcePath = $this->sourcePath($image);

        if ($sourcePath === null) {
            return $image->src();
        }

        $targetPath = $this->buildTargetPath($image, 'tojpg-q'.($this->quality ?? 'default'), 'jpg');
        $existing = $this->existingTargetUrl($image, $targetPath);

        if ($existing !== null) {
            return $existing;
        }

        $editor = $this->editor($image);

        if (is_wp_error($editor) || ! is_string($targetPath)) {
            return $image->src();
        }

        $this->applyQuality($editor, $this->quality);

        return $this->saveEditor($image, $editor, $targetPath, 'image/jpeg');
    }
}