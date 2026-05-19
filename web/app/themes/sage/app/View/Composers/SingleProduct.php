<?php

namespace App\View\Composers;

use App\Catalog\SingleProductView;
use Roots\Acorn\View\Composer;
use WC_Product;

class SingleProduct extends Composer
{
    /**
     * @var array<int, string>
     */
    protected static $views = ['woocommerce.content-single-product'];

    protected function with(): array
    {
        global $product;

        if (!$product instanceof WC_Product) {
            $product = wc_get_product(get_the_ID());
        }

        if (!$product instanceof WC_Product) {
            return [
                'productView' => null,
                'relatedProducts' => [],
            ];
        }

        $productView = new SingleProductView($product);

        return [
            'productView' => $productView->toArray(),
            'relatedProducts' => $productView->relatedProducts(),
        ];
    }
}