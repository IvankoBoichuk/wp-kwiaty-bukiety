<?php

namespace App\Catalog;

use App\Media\Image;
use WC_Product;

final class Product
{
    /**
     * @param  array<int, string>  $badges
     */
    public function __construct(
        public readonly int $productId,
        public readonly string $name,
        public readonly string $link,
        public readonly string $target,
        public readonly array $badges,
        public readonly string $price,
        public readonly Image $image,
    ) {}
    public static function fromWooCommerce(WC_Product $product): self
    {
        $thumbnailId = $product->get_image_id();
        $badges = [];

        if ($product->is_on_sale()) {
            $badges[] = __('Sale', 'sage-front');
        }

        if ($product->is_featured()) {
            $badges[] = __('Featured', 'sage-front');
        }

        return new self(
            productId: $product->get_id(),
            name: $product->get_name(),
            link: (string) (get_permalink($product->get_id()) ?: ''),
            target: '_self',
            badges: $badges,
            price: $product->get_price_html(),
            image: Image::fromAttachmentId(
                $thumbnailId,
                'medium',
                $product->get_name(),
                wc_placeholder_img_src(),
            ),
        );
    }
}
