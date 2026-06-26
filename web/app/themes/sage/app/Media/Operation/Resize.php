<?php

namespace App\Media\Operation;

use App\Media\Image;

final class Resize extends AbstractOperation
{
    public function __construct(
        private readonly ?int $width,
        private readonly ?int $height = null,
        private readonly bool $crop = false,
        private readonly ?int $quality = null,
    ) {}

    public function apply(Image $image): string
    {
        $sourcePath = $this->sourcePath($image);

        if ($sourcePath === null) {
            return $image->src();
        }

        $width = $this->width !== null && $this->width > 0 ? $this->width : null;
        $height = $this->height !== null && $this->height > 0 ? $this->height : null;

        if ($width === null && $height === null) {
            return $image->src();
        }

        $signature = sprintf(
            'resize-%sx%s-%s-q%s',
            $width ?? 'auto',
            $height ?? 'auto',
            $this->crop ? 'crop' : 'fit',
            $this->quality ?? 'default',
        );

        $targetPath = $this->buildTargetPath($image, $signature);
        $existing = $this->existingTargetUrl($image, $targetPath);

        if ($existing !== null) {
            return $existing;
        }

        $editor = $this->editor($image);

        if (is_wp_error($editor)) {
            return $image->src();
        }

        $this->applyQuality($editor, $this->quality);

        $result = $editor->resize($width, $height, $this->crop);

        if (is_wp_error($result) || ! is_string($targetPath)) {
            return $image->src();
        }

        return $this->saveEditor($image, $editor, $targetPath);
    }
}
