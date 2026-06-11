<?php

/**
 * Theme filters.
 */

namespace App;

use App\Blocks\Blocks;
use App\Catalog\Category;
use App\Catalog\Product;
use App\Catalog\Review;
use App\Catalog\Settings;
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
        __('Continued', 'sage-front'),
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
    'woocommerce_checkout_fields',
    function ($fields) {
        $fields['shipping']['shipping_first_name']['class'] = [];
        $fields['shipping']['shipping_first_name']['label'] = __(
            'Full name', //'Imię i nazwisko',
            'sage-front',
        );
        $fields['shipping']['shipping_first_name']['placeholder'] = __(
            'e.g. John Doe', //'np. Jan Kowalski'
            'sage-front',
        );
        $fields['shipping']['shipping_first_name']['priority'] = 4;
        $fields['shipping']['shipping_first_name']['required'] = true;

        $fields['billing']['billing_email']['required'] = true;
        $fields['billing']['billing_email']['placeholder'] = __(
            'e.g. john.doe@example.com', //'np. jan.kowalski@example.com',
            'sage-front',
        );
        $fields['billing']['billing_email']['input_class'] = [];
        $fields['billing']['billing_email']['custom_attributes'] = [
            'inputmode' => 'email',
        ];

        $fields['billing']['billing_first_name']['class'] = [];
        $fields['billing']['billing_first_name']['placeholder'] = __(
            'e.g. John', //'np. Jan',
            'sage-front',
        );
        $fields['billing']['billing_last_name']['class'] = [];
        $fields['billing']['billing_last_name']['placeholder'] = __(
            'e.g. Doe', //'np. Kowalski',
            'sage-front',
        );
        $fields['billing']['billing_last_name']['required'] = true;
        $fields['billing']['billing_phone']['placeholder'] = __(
            'e.g. 123 456 789', //'np. 123 456 789',
            'sage-front',
        );
        $fields['billing']['billing_phone']['custom_attributes'] = [
            'inputmode' => 'tel',
        ];

        $fields['billing']['billing_nip'] = [
            'type' => 'text',
            'label' => __('NIP', 'sage-front'),
            'placeholder' => __('e.g. 1234567890', 'sage-front'),
            'required' => false,
            'class' => ['form-row-wide'],
            'clear' => true,
            'priority' => 10000,
            'custom_attributes' => ['inputmode' => 'numeric'],
        ];

        $fields['shipping']['shipping_phone'] = [
            'type' => 'tel',
            'label' => __('Phone number', 'sage-front'),
            'placeholder' => __('e.g. 123 456 789', 'sage-front'),
            'required' => true,
            'priority' => 100,
            'custom_attributes' => ['inputmode' => 'tel'],
        ];

        $fields['shipping']['shipping_postcode']['priority'] = 2;
        $fields['shipping']['shipping_postcode']['placeholder'] = __(
            'e.g. 00-001',
            'sage-front',
        );
        $fields['shipping']['shipping_postcode']['label'] = __(
            'Postal code',
            'sage-front',
        );
        $fields['shipping']['shipping_postcode']['required'] = true;
        $fields['shipping']['shipping_postcode']['custom_attributes'] = [
            'inputmode' => 'numeric',
        ];

        $fields['shipping']['shipping_city']['priority'] = 3;
        $fields['shipping']['shipping_city']['placeholder'] = __(
            'e.g. Warsaw',
            'sage-front',
        );
        $fields['shipping']['shipping_city']['label'] = __(
            'City',
            'sage-front',
        );
        $fields['shipping']['shipping_city']['required'] = true;

        $fields['shipping']['shipping_place_name'] = [
            'type' => 'text',
            'label' => __('Place name', 'sage-front'),
            'placeholder' => __('e.g. Hotel Marriott', 'sage-front'),
            'priority' => 5,
            'required' => true,
        ];

        $fields['shipping']['shipping_address_1']['label'] = __(
            'Street and number',
            'sage-front',
        );
        $fields['shipping']['shipping_address_1']['placeholder'] = __(
            'e.g. ul. Marszałkowska 1/5',
            'sage-front',
        );
        $fields['shipping']['shipping_address_1']['priority'] = 1;
        $fields['shipping']['shipping_address_1']['required'] = true;

        $fields['shipping']['shipping_type_of_place'] = [
            'type' => 'select',
            'label' => __('Delivery place', 'sage-front'),
            'placeholder' => __('Delivery place', 'sage-front'),
            'default' => 'placeholder',
            'options' => Settings::locationsOptions(),
            'required' => true,
            'class' => ['form-row-wide'],
            'clear' => true,
            'priority' => 0,
        ];

        foreach (
            [
                'billing_company',
                'billing_country',
                'billing_state',
                'billing_city',
                'billing_postcode',
                'billing_address_1',
                'billing_address_2',
                'shipping_company',
                'shipping_country',
                'shipping_state',
                'shipping_address_2',
            ]
            as $fieldKey
        ) {
            if (str_starts_with($fieldKey, 'billing_')) {
                unset($fields['billing'][$fieldKey]);
                continue;
            }

            unset($fields['shipping'][$fieldKey]);
        }

        $placeholderMap = [
            'order_comments' => __(
                'Notes about your order, e.g. specials notes for delivery',
                'sage-front',
            ),
        ];

        unset($fields['shipping']['shipping_last_name']);

        foreach ($placeholderMap as $fieldKey => $placeholder) {
            foreach (['billing', 'shipping', 'order'] as $groupKey) {
                if (!isset($fields[$groupKey][$fieldKey])) {
                    continue;
                }

                $fields[$groupKey][$fieldKey]['placeholder'] = $placeholder;
            }
        }

        foreach ($fields as $typeKey => &$typeFields) {
            unset($typeKey);

            foreach ($typeFields as $fieldKey => &$field) {
                unset($fieldKey);

                if (!isset($field['placeholder']) || !$field['placeholder']) {
                    $field['placeholder'] = isset($field['label'])
                        ? $field['label']
                        : 'placeholder';
                }
            }
        }

        unset($typeFields, $field);

        return $fields;
    },
    20,
);

function getAddToCartRequestPayload(?\WP_REST_Request $request = null): array
{
    static $jsonPayload;

    $source = [];

    if ($request instanceof \WP_REST_Request) {
        $source = $request->get_params();
    } elseif ($_POST !== []) {
        $source = wp_unslash($_POST);
    } else {
        if ($jsonPayload === null) {
            $rawPayload = file_get_contents('php://input');
            $decodedPayload = is_string($rawPayload)
                ? json_decode($rawPayload, true)
                : null;

            $jsonPayload = is_array($decodedPayload) ? $decodedPayload : [];
        }

        $source = $jsonPayload;
    }

    return [
        'delivery_date' => sanitize_text_field(
            (string) ($source['delivery_date'] ??
                ($source['deliveryDate'] ?? '')),
        ),
        'delivery_time' => sanitize_text_field(
            (string) ($source['delivery_time'] ??
                ($source['deliveryTime'] ?? '')),
        ),
        'card_message' => sanitize_textarea_field(
            (string) ($source['card_message'] ??
                ($source['cardMessage'] ?? '')),
        ),
        'addition_ids' => array_values(
            array_filter(
                array_map(
                    'absint',
                    (array) ($source['addition_ids'] ??
                        ($source['additionIds'] ?? [])),
                ),
            ),
        ),
    ];
}

add_filter(
    'woocommerce_add_cart_item_data',
    function ($cartItemData, $productId) {
        $payload = getAddToCartRequestPayload();
        $deliveryDate = $payload['delivery_date'];
        $deliveryTime = $payload['delivery_time'];
        $cardMessage = $payload['card_message'];
        $additionIds = $payload['addition_ids'];

        if (function_exists('WC') && WC()->session) {
            WC()->session->set('delivery_date', $deliveryDate);
            WC()->session->set('delivery_time', $deliveryTime);
        }

        if ($cardMessage !== '') {
            $cartItemData['card_message'] = $cardMessage;
        }

        if ($additionIds !== []) {
            $cartItemData['addition_ids'] = $additionIds;
        }

        if ($additionIds !== [] || $cardMessage !== '') {
            $cartItemData['unique_key'] = md5(
                (string) wp_json_encode([
                    $productId,
                    $additionIds,
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
    'woocommerce_store_api_add_to_cart_data',
    function ($add_to_cart_data, \WP_REST_Request $request) {
        $payload = getAddToCartRequestPayload($request);
        $deliveryDate = $payload['delivery_date'];
        $deliveryTime = $payload['delivery_time'];
        $cardMessage = $payload['card_message'];
        $additionIds = $payload['addition_ids'];

        $cartItemData = is_array($add_to_cart_data['cart_item_data'] ?? null)
            ? $add_to_cart_data['cart_item_data']
            : [];

        if (function_exists('WC') && WC()->session) {
            WC()->session->set('delivery_date', $deliveryDate);
            WC()->session->set('delivery_time', $deliveryTime);
        }

        if ($cardMessage !== '') {
            $cartItemData['card_message'] = $cardMessage;
        }

        if ($additionIds !== []) {
            $cartItemData['addition_ids'] = $additionIds;
        }

        if ($cardMessage !== '' || $additionIds !== []) {
            $cartItemData['unique_key'] = md5(
                (string) wp_json_encode([
                    $add_to_cart_data['id'] ?? 0,
                    $additionIds,
                    $cardMessage,
                ]),
            );
        }

        $add_to_cart_data['cart_item_data'] = $cartItemData;

        return $add_to_cart_data;
    },
    10,
    2,
);

add_action(
    'woocommerce_store_api_validate_add_to_cart',
    function ($product, $request) {
        unset($product);

        $additionIds = array_values(
            array_filter(
                array_map('absint', (array) ($request['additionIds'] ?? [])),
            ),
        );

        foreach ($additionIds as $additionId) {
            if (!(wc_get_product($additionId) instanceof \WC_Product)) {
                throw new \Exception(
                    __('One of the additions was not found.', 'sage-front'),
                );
            }
        }
    },
    10,
    2,
);

add_action(
    'woocommerce_add_to_cart',
    function (
        $cartItemKey,
        $productId,
        $quantity,
        $variationId,
        $variation,
        $cartItemData,
    ) {
        unset($productId, $variationId, $variation);

        if (
            !is_array($cartItemData) ||
            !empty($cartItemData['is_sage_addition']) ||
            empty($cartItemData['addition_ids']) ||
            !is_array($cartItemData['addition_ids']) ||
            !(function_exists('WC') && WC()->cart instanceof \WC_Cart)
        ) {
            return;
        }

        $addedItemKeys = [];

        foreach ($cartItemData['addition_ids'] as $additionId) {
            $additionCartItemKey = WC()->cart->add_to_cart(
                absint($additionId),
                max(1, (int) $quantity),
                0,
                [],
                ['is_sage_addition' => true],
            );

            if ($additionCartItemKey !== false) {
                $addedItemKeys[] = $additionCartItemKey;
                continue;
            }

            foreach ($addedItemKeys as $addedItemKey) {
                WC()->cart->remove_cart_item($addedItemKey);
            }

            WC()->cart->remove_cart_item((string) $cartItemKey);

            if (function_exists('wc_add_notice')) {
                wc_add_notice(
                    __(
                        'Unable to add one of the additions to cart.',
                        'sage-front',
                    ),
                    'error',
                );
            }

            break;
        }
    },
    10,
    6,
);

add_filter(
    'woocommerce_get_item_data',
    function ($itemData, $cartItem) {
        if (!empty($cartItem['card_message'])) {
            $itemData[] = [
                'key' => __('Card message content', 'sage-front'),
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
                    'key' => __('Add-ons', 'sage-front'),
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

        if (!empty($values['card_message'])) {
            $item->add_meta_data(
                __('Card message content', 'sage-front'),
                wc_clean((string) $values['card_message']),
                true,
            );
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
                __('Add-ons', 'sage-front'),
                wc_clean(implode(', ', $additionNames)),
                true,
            );
        }
    },
    10,
    3,
);

add_action(
    'woocommerce_checkout_create_order',
    function ($order, $data) {
        foreach (
            [
                'shipping_type_of_place' => __(
                    'Delivery place type',
                    'sage-front',
                ),
                'shipping_place_name' => __('Place name', 'sage-front'),
                'shipping_phone' => __('Recipient phone', 'sage-front'),
                'billing_nip' => __('NIP', 'sage-front'),
            ]
            as $fieldKey => $metaLabel
        ) {
            if (empty($data[$fieldKey])) {
                continue;
            }

            $order->update_meta_data(
                $metaLabel,
                wc_clean((string) $data[$fieldKey]),
            );
        }

        if (function_exists('WC') && WC()->session) {
            $deliveryDate = (string) WC()->session->get('delivery_date');
            $deliveryTime = (string) WC()->session->get('delivery_time');

            if ($deliveryDate !== '') {
                $order->update_meta_data(
                    'delivery_date',
                    wc_clean($deliveryDate),
                );
            }

            if ($deliveryTime !== '') {
                $order->update_meta_data(
                    'delivery_time',
                    wc_clean($deliveryTime),
                );
            }
        }
    },
    10,
    2,
);

add_action(
    'woocommerce_after_checkout_validation',
    function ($data, $errors) {
        $shippingTypeOfPlace = wc_clean(
            (string) ($data['shipping_type_of_place'] ?? ''),
        );
        $shippingPlaceName = wc_clean(
            (string) ($data['shipping_place_name'] ?? ''),
        );

        if (
            $shippingTypeOfPlace === 'private-address' &&
            $shippingPlaceName === '' &&
            $errors instanceof \WP_Error
        ) {
            $errors->add(
                'shipping_place_name_required',
                __('Please provide the place name.', 'sage-front'),
            );
        }
    },
    10,
    2,
);

add_action(
    'woocommerce_store_api_checkout_update_order_from_request',
    function (\WC_Order $order, \WP_REST_Request $request) {
        $additionalFields = (array) ($request['additional_fields'] ?? []);

        foreach (
            [
                'shipping_type_of_place' => __(
                    'Delivery place type',
                    'sage-front',
                ),
                'shipping_place_name' => __('Place name', 'sage-front'),
                'shipping_phone' => __('Recipient phone', 'sage-front'),
                'billing_nip' => __('NIP', 'sage-front'),
            ]
            as $fieldKey => $metaLabel
        ) {
            if (empty($additionalFields[$fieldKey])) {
                continue;
            }

            $order->update_meta_data(
                $metaLabel,
                wc_clean((string) $additionalFields[$fieldKey]),
            );
        }

        if (function_exists('WC') && WC()->session) {
            $deliveryDate = (string) WC()->session->get('delivery_date');
            $deliveryTime = (string) WC()->session->get('delivery_time');

            if ($deliveryDate !== '') {
                $order->update_meta_data(
                    'delivery_date',
                    wc_clean($deliveryDate),
                );
            }

            if ($deliveryTime !== '') {
                $order->update_meta_data(
                    'delivery_time',
                    wc_clean($deliveryTime),
                );
            }
        }
    },
    10,
    2,
);

add_action('woocommerce_before_add_to_cart_button', function () {
    echo '<input type="hidden" name="delivery_date" value="" data-delivery-date-hidden>';
    echo '<input type="hidden" name="delivery_time" value="" data-delivery-time-hidden>';
    echo '<input type="hidden" name="card_message" value="" data-card-message-hidden>';
    echo '<div data-addition-inputs hidden></div>';
});

remove_action('woocommerce_thankyou', 'woocommerce_order_details_table', 10);
remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);

add_action('woocommerce_after_shop_loop', function () {
    global $wp_query;

    if (!($wp_query instanceof \WP_Query)) {
        return;
    }

    $currentPage = max(
        1,
        (int) get_query_var('paged'),
        (int) get_query_var('page'),
    );
    $maxPages = (int) $wp_query->max_num_pages;

    if ($maxPages <= 1 || $currentPage >= $maxPages) {
        return;
    }

    $nextPageUrl = get_pagenum_link($currentPage + 1);

    if (!is_string($nextPageUrl) || $nextPageUrl === '') {
        return;
    }

    printf(
        '<div class="mt-8 flex justify-center" data-products-load-more><button type="button" data-products-load-more-button data-next-url="%s" data-default-label="%s" data-loading-label="%s" class="%s"><span>%s</span></button></div>',
        esc_url($nextPageUrl),
        esc_attr__('Show more', 'sage-front'),
        esc_attr__('Loading...', 'sage-front'),
        esc_attr(Blocks::buttonClasses('border', 'md', false)),
        esc_html__('Show more', 'sage-front'),
    );
});
