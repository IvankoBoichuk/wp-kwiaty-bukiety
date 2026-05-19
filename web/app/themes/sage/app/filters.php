<?php

/**
 * Theme filters.
 */

namespace App;

use App\Catalog\Category;
use App\Catalog\Product;
use App\Catalog\Review;
use WP_Term_Query;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(
        ' &hellip; <a href="%s">%s</a>',
        get_permalink(),
        __('Continued', 'sage'),
    );
});

function bumpProductsBlockCacheVersion(): void
{
    update_option(
        'sage_blocks_products_cache_version',
        (string) microtime(true),
        false,
    );
}

add_filter('sage/blocks/categories', function ($categories) {
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'include' => $categories,
        'orderby' => 'include',
    ]);
    $categories = array_map(
        fn($category) => Category::fromWordPressTerm($category),
        $categories,
    );
    return $categories;
});

add_filter('sage/blocks/products', function ($products) {
    $productIds = array_values(
        array_unique(
            array_filter(
                array_map('absint', is_array($products) ? $products : []),
            ),
        ),
    );

    if ($productIds === []) {
        return [];
    }

    $cacheKey = sprintf(
        'sage_blocks_products_%s',
        md5(
            (string) get_option('sage_blocks_products_cache_version', '1') .
                '|' .
                wp_json_encode($productIds),
        ),
    );
    $cached = get_transient($cacheKey);

    if (\is_array($cached) && wp_get_environment_type() !== 'development') {
        return $cached;
    }

    $resolved = [];

    foreach ($productIds as $productId) {
        $product = wc_get_product($productId);

        if ($product) {
            $resolved[] = Product::fromWooCommerce($product);
        }
    }

    set_transient(
        $cacheKey,
        $resolved,
        (int) apply_filters(
            'sage/blocks/products/cache_ttl',
            HOUR_IN_SECONDS,
            $productIds,
        ),
    );

    return $resolved;
});

add_action(
    'save_post_product',
    __NAMESPACE__ . '\\bumpProductsBlockCacheVersion',
);
add_action(
    'save_post_product_variation',
    __NAMESPACE__ . '\\bumpProductsBlockCacheVersion',
);

add_filter('sage/blocks/cities', function ($cities) {
    $cities = new WP_Term_Query([
        'taxonomy' => 'product_cat',
        'include' => $cities,
        'orderby' => 'include',
        'hide_empty' => false,
    ]);
    return $cities;
});

add_filter('sage/blocks/reviews', function ($reviews) {
    $resolved = [];

    foreach ($reviews as $item) {
        $reviewId = absint($item);

        if ($reviewId <= 0) {
            continue;
        }

        $comment = get_comment($reviewId);

        if (!($comment instanceof \WP_Comment)) {
            continue;
        }

        // if ((int) $comment->comment_post_ID <= 0 || get_post_type((int) $comment->comment_post_ID) !== 'product') {
        //     continue;
        // }

        $resolved[] = Review::fromWordPressComment($comment);
    }

    return $resolved;
});

add_filter(
    'woocommerce_format_price_range',
    fn($price, $from, $to) => \sprintf(
        __('From: %s', 'sage-front'),
        wc_price($from),
    ),
    10,
    3,
);
add_filter('woocommerce_available_variation', function ($data) {
    $variation = wc_get_product($data['variation_id']);

    $attributes = $variation->get_attributes();

    $attribute_values = [];

    foreach ($attributes as $attribute_name => $attribute_value) {
        // Якщо це taxonomy (pa_color, pa_size)
        if (taxonomy_exists($attribute_name)) {
            $term = get_term_by('slug', $attribute_value, $attribute_name);
            if ($term && !is_wp_error($term)) {
                $attribute_values[] = $term->name;
            }
        } else {
            // custom attribute
            $attribute_values[] = $attribute_value;
        }
    }

    $data['name'] = implode(', ', $attribute_values);

    return $data;
});

add_filter(
    'woocommerce_add_cart_item_data',
    function ($cartItemData, $productId) {
        $deliveryDate = sanitize_text_field(
            wp_unslash($_POST['delivery_date'] ?? ''),
        );
        $deliveryTime = sanitize_text_field(
            wp_unslash($_POST['delivery_time'] ?? ''),
        );
        $cardMessage = sanitize_textarea_field(
            wp_unslash($_POST['card_message'] ?? ''),
        );
        $additionIds = array_values(
            array_filter(
                array_map('absint', (array) ($_POST['addition_ids'] ?? [])),
            ),
        );

        if ($deliveryDate !== '') {
            $cartItemData['delivery_date'] = $deliveryDate;
        }

        if ($deliveryTime !== '') {
            $cartItemData['delivery_time'] = $deliveryTime;
        }

        if ($cardMessage !== '') {
            $cartItemData['card_message'] = $cardMessage;
        }

        if ($additionIds !== []) {
            $cartItemData['addition_ids'] = $additionIds;
            $cartItemData['unique_key'] = md5(
                (string) wp_json_encode([
                    $productId,
                    $additionIds,
                    $deliveryDate,
                    $deliveryTime,
                    $cardMessage,
                ]),
            );
        }

        return $cartItemData;
    },
    10,
    2,
);

add_filter(
    'woocommerce_get_item_data',
    function ($itemData, $cartItem) {
        if (!empty($cartItem['delivery_date'])) {
            $itemData[] = [
                'key' => __('Data dostawy', 'sage-front'),
                'value' => wc_clean((string) $cartItem['delivery_date']),
            ];
        }

        if (!empty($cartItem['delivery_time'])) {
            $itemData[] = [
                'key' => __('Przedzial dostawy', 'sage-front'),
                'value' => wc_clean((string) $cartItem['delivery_time']),
            ];
        }

        if (!empty($cartItem['card_message'])) {
            $itemData[] = [
                'key' => __('Tresc bileciku', 'sage-front'),
                'value' => wc_clean((string) $cartItem['card_message']),
            ];
        }

        if (
            !empty($cartItem['addition_ids']) &&
            is_array($cartItem['addition_ids'])
        ) {
            $additionNames = [];

            foreach ($cartItem['addition_ids'] as $additionId) {
                $addition = wc_get_product(absint($additionId));

                if ($addition instanceof \WC_Product) {
                    $additionNames[] = $addition->get_name();
                }
            }

            if ($additionNames !== []) {
                $itemData[] = [
                    'key' => __('Dodatki', 'sage-front'),
                    'value' => wc_clean(implode(', ', $additionNames)),
                ];
            }
        }

        return $itemData;
    },
    10,
    2,
);

add_action(
    'woocommerce_checkout_create_order_line_item',
    function ($item, $cartItemKey, $values) {
        unset($cartItemKey);

        foreach (
            [
                'delivery_date' => __('Data dostawy', 'sage-front'),
                'delivery_time' => __('Przedzial dostawy', 'sage-front'),
                'card_message' => __('Tresc bileciku', 'sage-front'),
            ]
            as $key => $label
        ) {
            if (!empty($values[$key])) {
                $item->add_meta_data(
                    $label,
                    wc_clean((string) $values[$key]),
                    true,
                );
            }
        }

        if (
            empty($values['addition_ids']) ||
            !is_array($values['addition_ids'])
        ) {
            return;
        }

        $additionNames = [];

        foreach ($values['addition_ids'] as $additionId) {
            $addition = wc_get_product(absint($additionId));

            if ($addition instanceof \WC_Product) {
                $additionNames[] = $addition->get_name();
            }
        }

        if ($additionNames !== []) {
            $item->add_meta_data(
                __('Dodatki', 'sage-front'),
                wc_clean(implode(', ', $additionNames)),
                true,
            );
        }
    },
    10,
    3,
);

add_action('woocommerce_before_add_to_cart_button', function () {
    echo '<input type="hidden" name="delivery_date" value="" data-delivery-date-hidden>';
    echo '<input type="hidden" name="delivery_time" value="" data-delivery-time-hidden>';
    echo '<input type="hidden" name="card_message" value="" data-card-message-hidden>';
    echo '<div data-addition-inputs hidden></div>';
});
