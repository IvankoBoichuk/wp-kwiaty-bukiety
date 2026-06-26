<?php

namespace App\Media;

interface ImageOperation
{
    public function apply(Image $image): string;
}
