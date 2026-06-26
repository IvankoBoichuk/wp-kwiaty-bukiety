<?php

namespace App\View\Composers;

use App\Catalog\ProductData;
use App\Catalog\SingleProductView;
use App\Support\DeliveryTimer;
use Roots\Acorn\View\Composer;
use WC_Product;

class SingleProduct extends Composer
{
    /**
     * @var array<int, string>
     */
    protected static $views = [
        'woocommerce.single-product',
        'woocommerce.content-single-product',
        'woocommerce.content-single-product-funeral',
    ];

    protected function with(): array
    {
        global $product;

        if (!($product instanceof WC_Product)) {
            $product = wc_get_product(get_the_ID());
        }

        if (!($product instanceof WC_Product)) {
            return [
                'singleProductContentView'
                    => 'woocommerce.content-single-product',
                'productView' => null,
                'relatedProducts' => [],
                'deliverySchedule' => app(
                    DeliveryTimer::class,
                )->purchaseOptions(),
            ];
        }

        $productData = new ProductData($product);
        $productView = new SingleProductView($product);

        return [
            'singleProductContentView' => $productData->isFuneral()
                ? 'woocommerce.content-single-product-funeral'
                : 'woocommerce.content-single-product',
            'productView' => $productView->toArray(),
            'relatedProducts' => $productView->relatedProducts(),
            'deliverySchedule' => app(DeliveryTimer::class)->purchaseOptions(),
        ];
    }
}
