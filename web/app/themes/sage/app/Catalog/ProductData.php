<?php

namespace App\Catalog;

use WC_Product;

final class ProductData
{
    /**
     * @var array<string, mixed>
     */
    private array $cache = [];

    public function __construct(private readonly WC_Product $product) {}

    /**
     * @return array<int, string>
     */
    public function badges(): array
    {
        if (isset($this->cache['badges'])) {
            return $this->cache['badges'];
        }

        $badges = [];

        if ($this->product->is_on_sale()) {
            $badges[] = __('Promotion', 'sage-front');
        }

        if ($this->product->is_featured()) {
            $badges[] = __('Recommended', 'sage-front');
        }

        $this->cache['badges'] = $badges;

        return $this->cache['badges'];
    }

    /**
     * @return array<int, \WP_Comment>
     */
    public function approvedReviewComments(int $limit = 4): array
    {
        $cacheKey = 'approvedReviewComments:' . $limit;

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $comments = get_comments([
            'post_id' => $this->product->get_id(),
            'status' => 'approve',
            'type' => 'review',
            'number' => $limit,
        ]);

        $this->cache[$cacheKey] = array_values(
            array_filter(
                $comments,
                fn($comment) => $comment instanceof \WP_Comment,
            ),
        );

        return $this->cache[$cacheKey];
    }

    /**
     * @return array<int, Product>
     */
    public function relatedProducts(int $limit = 6): array
    {
        $cacheKey = 'relatedProducts:' . $limit;

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $relatedIds = wc_get_related_products($this->product->get_id(), $limit);

        if ($relatedIds === []) {
            $this->cache[$cacheKey] = [];

            return $this->cache[$cacheKey];
        }

        $this->cache[$cacheKey] = array_values(
            array_filter(
                array_map(function ($id) {
                    $related = wc_get_product($id);

                    if (!($related instanceof WC_Product)) {
                        return null;
                    }

                    return Product::fromWooCommerce($related);
                }, $relatedIds),
            ),
        );

        return $this->cache[$cacheKey];
    }

    public function isFuneral(): bool
    {
        if (isset($this->cache['isFuneral'])) {
            return $this->cache['isFuneral'];
        }

        $this->cache['isFuneral'] = has_term(
            'wieniec-pogrzebowa',
            'product_cat',
            $this->product->get_id(),
        );

        return $this->cache['isFuneral'];
    }
}
