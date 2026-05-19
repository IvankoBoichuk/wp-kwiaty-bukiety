<?php

namespace App\Catalog;

use App\Media\Image;
use WC_Product;
use WC_Product_Variation;
use WC_Product_Variable;

final class SingleProductView
{
    public function __construct(public readonly WC_Product $product) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->product->get_id(),
            'title' => $this->product->get_name(),
            'priceHtml' => $this->product->get_price_html(),
            'shortDescription' => (string) $this->product->get_short_description(),
            'description' => (string) $this->product->get_description(),
            'sku' => (string) $this->product->get_sku(),
            'badges' => $this->badges(),
            'gallery' => $this->gallery(),
            'variations' => $this->variations(),
            'additions' => $this->upsells(),
            'reviews' => $this->reviews(),
            'reviewCount' => (int) $this->product->get_review_count(),
            'averageRating' => (float) $this->product->get_average_rating(),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function badges(): array
    {
        $badges = [];

        if ($this->product->is_on_sale()) {
            $badges[] = __('Promocja', 'sage-front');
        }

        if ($this->product->is_featured()) {
            $badges[] = __('Polecany', 'sage-front');
        }

        return $badges;
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    protected function gallery(): array
    {
        $imageIds = array_values(
            array_filter(
                array_merge(
                    [$this->product->get_image_id()],
                    $this->product->get_gallery_image_ids(),
                ),
            ),
        );

        if ($imageIds === []) {
            return [
                [
                    'src' => wc_placeholder_img_src('large'),
                    'alt' => $this->product->get_name(),
                    'width' => 0,
                    'height' => 0,
                    'srcset' => '',
                    'sizes' => '',
                ],
            ];
        }

        return array_map(function (int $attachmentId): array {
            $image = Image::fromAttachmentId(
                $attachmentId,
                'large',
                $this->product->get_name(),
                wc_placeholder_img_src('large'),
            );

            return [
                'src' => $image->src(),
                'alt' => $image->alt(),
                'width' => $image->width() ?? 0,
                'height' => $image->height() ?? 0,
                'srcset' => $image->srcset(),
                'sizes' => $image->sizes(),
            ];
        }, $imageIds);
    }

    /**
     * @return array<int, array<string, int|string>>
     */
    protected function variations(): array
    {
        if (!($this->product instanceof WC_Product_Variable)) {
            return [
                [
                    'id' => $this->product->get_id(),
                    'name' => __('Standard', 'sage-front'),
                    'price' => wp_strip_all_tags(
                        $this->product->get_price_html(),
                    ),
                    'description' =>
                        $this->product->get_short_description() !== ''
                            ? wp_strip_all_tags(
                                $this->product->get_short_description(),
                            )
                            : __('Gotowy do zamowienia od razu.', 'sage-front'),
                ],
            ];
        }

        $variations = [];

        foreach ($this->product->get_available_variations() as $variationData) {
            $variationId = absint($variationData['variation_id'] ?? 0);

            if ($variationId <= 0) {
                continue;
            }

            $variation = wc_get_product($variationId);

            if (!($variation instanceof WC_Product_Variation)) {
                continue;
            }

            $attributeSummary = wc_get_formatted_variation(
                $variation,
                true,
                false,
                true,
            );
            $description =
                $variation->get_description() ?:
                $variation->get_short_description();

            $variations[] = [
                'id' => $variationId,
                'name' => $variation->get_name(),
                'price' => wp_strip_all_tags($variation->get_price_html()),
                'description' =>
                    $description !== ''
                        ? wp_strip_all_tags($description)
                        : wp_strip_all_tags((string) $attributeSummary),
            ];
        }

        return $variations;
    }

    /**
     * @return array<int, Product>
     */
    protected function upsells(): array
    {
        $products = [];

        foreach ($this->product->get_upsell_ids() as $id) {
            $upsell = wc_get_product($id);

            if ($upsell instanceof WC_Product) {
                $products[] = Product::fromWooCommerce($upsell);
            }
        }

        return $products;
    }

    /**
     * @return array<int, array<string, int|float|string>>
     */
    protected function reviews(): array
    {
        $comments = get_comments([
            'post_id' => $this->product->get_id(),
            'status' => 'approve',
            'type' => 'review',
            'number' => 4,
        ]);

        $reviews = [];

        foreach ($comments as $comment) {
            if ($comment instanceof \WP_Comment) {
                $reviews[] = Review::fromWordPressComment($comment)->toArray();
            }
        }

        return $reviews;
    }

    /**
     * @return array<int, Product>
     */
    public function relatedProducts(int $limit = 6): array
    {
        $products = [];
        $relatedIds = wc_get_related_products($this->product->get_id(), $limit);

        foreach ($relatedIds as $id) {
            $related = wc_get_product($id);

            if ($related instanceof WC_Product) {
                $products[] = Product::fromWooCommerce($related);
            }
        }

        return $products;
    }
}
