<?php

namespace App\Media\Operation;

use App\Media\Image;

final class Retina extends AbstractOperation
{
    public function __construct(
        private readonly int $width,
        private readonly ?int $height = null,
        private readonly bool $crop = false,
        private readonly ?int $quality = null,
    ) {}

    public function apply(Image $image): string
    {
        $height = $this->height !== null && $this->height > 0 ? $this->height * 2 : null;

        return (new Resize($this->width * 2, $height, $this->crop, $this->quality))->apply($image);
    }
}