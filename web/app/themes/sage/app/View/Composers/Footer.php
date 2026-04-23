<?php

namespace App\View\Composers;

use App\Support\Context;
use Roots\Acorn\View\Composer;

class Footer extends Composer
{
    /**
     * @var array<int, string>
     */
    protected static $views = ['sections.footer'];

    protected function with(): array
    {
        $context = app(Context::class);

        return [
            'siteName' => $context->siteName(),
            'logos' => $context->logos(),
            'contacts' => $context->contacts(),
            'footerMenus' => $context->footerMenus(),
            'socials' => $context->socials(),
        ];
    }
}
