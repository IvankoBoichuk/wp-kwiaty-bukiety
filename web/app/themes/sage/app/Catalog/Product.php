<?php

namespace App\Catalog;

use App\Media\Image;
use WC_Product;
use WC_Product_Variable;
use WC_Product_Variation;

final class Product
{
    protected $cache = [];
    public readonly int $id;
    public readonly string $name;
    public readonly string $link;
    public readonly string $target;
    public readonly array $badges;
    public readonly string $price;
    public readonly Image $image;
    public function __construct(public readonly WC_Product $product)
    {
        $this->id = $product->get_id();
        $this->name = $product->get_name();
        $this->link = (string) (get_permalink($product->get_id()) ?: '');
        $this->target = '_self';
        $this->badges = [];

        if ($product->is_on_sale()) {
            $this->badges[] = __('Promocja', 'sage-front');
        }

        if ($product->is_featured()) {
            $this->badges[] = __('Polecany', 'sage-front');
        }

        $thumbnailId = $product->get_image_id();

        $this->price = $product->get_price_html();
        $this->image = Image::fromAttachmentId(
            $thumbnailId,
            'medium',
            $product->get_name(),
            wc_placeholder_img_src(),
        );
    }
    public static function fromWooCommerce(WC_Product $product): self
    {
        return new self($product);
    }
    public static function fromID(int $id): self
    {
        $product = wc_get_product($id);
        if (!$product) {
            throw new \InvalidArgumentException(
                "Product with ID $id not found.",
            );
        }
        return new self($product);
    }

    public function isVariable(): bool
    {
        return $this->product instanceof WC_Product_Variable;
    }

    /**
     * @return array<int, array<string, int|string>>
     */

    public function getVariations(): array
    {
        if (!($this->product instanceof WC_Product_Variable)) {
            return [];
        }

        if (isset($this->cache['getVariations'])) {
            return $this->cache['getVariations'];
        }

        $variations = $this->product->get_available_variations();
        $this->cache['getVariations'] = $variations;
        return $this->cache['getVariations'];
    }

    public function crossSellProducts(): array
    {
        if (isset($this->cache['crossSellProducts'])) {
            return $this->cache['crossSellProducts'];
        }
        $crossSells = $this->product->get_cross_sell_ids();

        if (empty($crossSells)) {
            return [];
        }

        $this->cache['crossSellProducts'] = array_map(
            fn($id) => self::fromID($id),
            $crossSells,
        );

        return $this->cache['crossSellProducts'];
    }
    public function description(): string
    {
        if (isset($this->cache['description'])) {
            return $this->cache['description'];
        }
        $this->cache['description'] = $this->product->get_description();
        return $this->cache['description'];
    }
    public function reviews(): array
    {
        if (isset($this->cache['reviews'])) {
            return $this->cache['reviews'];
        }
        $comments = get_comments([
            'post_id' => $this->product->get_id(),
            'status' => 'approve',
            'type' => 'review',
            'number' => 4,
        ]);

        $reviews = [];

        foreach ($comments as $comment) {
            if ($comment instanceof \WP_Comment) {
                $reviews[] = Review::fromWordPressComment($comment);
            }
        }

        $this->cache['reviews'] = $reviews;
        return $this->cache['reviews'];
    }
    public function relatedProducts(): array
    {
        if (isset($this->cache['relatedProducts'])) {
            return $this->cache['relatedProducts'];
        }
        $relatedIds = wc_get_related_products($this->product->get_id(), 6);

        if (empty($relatedIds)) {
            return [];
        }

        $this->cache['relatedProducts'] = array_map(
            fn($id) => self::fromID($id),
            $relatedIds,
        );

        return $this->cache['relatedProducts'];
    }
}
