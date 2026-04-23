<?php

namespace App\Media\Operation;

use App\Media\Image;

interface InterfaceOperation
{
    public function apply(Image $image): string;
}