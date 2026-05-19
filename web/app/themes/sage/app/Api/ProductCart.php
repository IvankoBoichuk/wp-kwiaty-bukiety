<?php

namespace App\Api;

use WC_Cart;
use WC_Product;
use WC_Product_Variable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class ProductCart
{
    protected const ROUTE_NAMESPACE = 'sage/v1';

    protected const ROUTE = '/cart/add';

    public static function boot(): void
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes(): void
    {
        register_rest_route(self::ROUTE_NAMESPACE, self::ROUTE, [
            'methods' => 'POST',
            'callback' => [self::class, 'handle'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle(WP_REST_Request $request): WP_REST_Response
    {
        if (!function_exists('WC')) {
            return new WP_REST_Response(
                [
                    'status' => 'error',
                    'message' => __(
                        'WooCommerce is unavailable.',
                        'sage-front',
                    ),
                ],
                500,
            );
        }

        if (function_exists('wc_load_cart')) {
            wc_load_cart();
        }

        $productId = absint($request->get_param('productId'));
        $quantity = max(1, absint($request->get_param('quantity')));
        $variationId = absint($request->get_param('variationId'));
        $attributes = self::normalizeAttributes(
            $request->get_param('attributes'),
        );
        $additionIds = self::normalizeAdditionIds(
            $request->get_param('additionIds'),
        );
        $cartItemData = self::buildCartItemData($request, $productId);

        if ($productId <= 0) {
            return new WP_REST_Response(
                [
                    'status' => 'error',
                    'message' => __('Invalid product ID.', 'sage-front'),
                ],
                400,
            );
        }

        $product = wc_get_product($productId);

        if (!($product instanceof WC_Product)) {
            return new WP_REST_Response(
                [
                    'status' => 'error',
                    'message' => __('Product not found.', 'sage-front'),
                ],
                404,
            );
        }

        if ($product instanceof WC_Product_Variable && $variationId <= 0) {
            return new WP_REST_Response(
                [
                    'status' => 'error',
                    'message' => __(
                        'Select a product variation.',
                        'sage-front',
                    ),
                ],
                400,
            );
        }

        $cart = WC()->cart;

        if (!($cart instanceof WC_Cart)) {
            return new WP_REST_Response(
                [
                    'status' => 'error',
                    'message' => __('Cart is unavailable.', 'sage-front'),
                ],
                500,
            );
        }

        $addedCartItemKey = $cart->add_to_cart(
            $productId,
            $quantity,
            $variationId,
            $attributes,
            $cartItemData,
        );

        if ($addedCartItemKey === false) {
            $notices = function_exists('wc_get_notices')
                ? wc_get_notices('error')
                : [];
            $message = self::extractErrorMessage($notices);

            if (function_exists('wc_clear_notices')) {
                wc_clear_notices();
            }

            return new WP_REST_Response(
                [
                    'status' => 'error',
                    'message' =>
                        $message ?:
                        __('Unable to add product to cart.', 'sage-front'),
                ],
                400,
            );
        }

        $addedItemKeys = [$addedCartItemKey];

        $additionError = self::addAdditionProducts(
            $additionIds,
            1,
            $addedItemKeys,
        );

        if ($additionError !== '') {
            self::rollbackCartItems($addedItemKeys);

            if (function_exists('wc_clear_notices')) {
                wc_clear_notices();
            }

            return new WP_REST_Response(
                [
                    'status' => 'error',
                    'message' => $additionError,
                ],
                400,
            );
        }

        if (function_exists('wc_clear_notices')) {
            wc_clear_notices();
        }

        return new WP_REST_Response([
            'status' => 'ok',
            'cartCount' => $cart->get_cart_contents_count(),
            'cartUrl' => function_exists('wc_get_cart_url')
                ? wc_get_cart_url()
                : home_url('/cart'),
            'itemKey' => $addedCartItemKey,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected static function normalizeAttributes(mixed $attributes): array
    {
        if (!is_array($attributes)) {
            return [];
        }

        $normalized = [];

        foreach ($attributes as $name => $value) {
            if (!is_string($name)) {
                continue;
            }

            $normalized[$name] = sanitize_text_field((string) $value);
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function buildCartItemData(
        WP_REST_Request $request,
        int $productId,
    ): array {
        $deliveryDate = sanitize_text_field(
            (string) $request->get_param('deliveryDate'),
        );
        $deliveryTime = sanitize_text_field(
            (string) $request->get_param('deliveryTime'),
        );
        $cardMessage = sanitize_textarea_field(
            (string) $request->get_param('cardMessage'),
        );

        $cartItemData = [];

        if ($deliveryDate !== '') {
            $cartItemData['delivery_date'] = $deliveryDate;
        }

        if ($deliveryTime !== '') {
            $cartItemData['delivery_time'] = $deliveryTime;
        }

        if ($cardMessage !== '') {
            $cartItemData['card_message'] = $cardMessage;
        }

        if (
            $deliveryDate !== '' ||
            $deliveryTime !== '' ||
            $cardMessage !== ''
        ) {
            $cartItemData['unique_key'] = md5(
                (string) wp_json_encode([
                    $productId,
                    $deliveryDate,
                    $deliveryTime,
                    $cardMessage,
                ]),
            );
        }

        return $cartItemData;
    }

    /**
     * @return array<int, int>
     */
    protected static function normalizeAdditionIds(mixed $additionIds): array
    {
        return array_values(
            array_filter(array_map('absint', (array) $additionIds)),
        );
    }

    /**
     * @param array<int, int> $additionIds
     * @param array<int, string> $addedItemKeys
     */
    protected static function addAdditionProducts(
        array $additionIds,
        int $quantity,
        array &$addedItemKeys,
    ): string {
        if ($additionIds === [] || !(WC()->cart instanceof WC_Cart)) {
            return '';
        }

        foreach ($additionIds as $additionId) {
            $additionProduct = wc_get_product($additionId);

            if (!($additionProduct instanceof WC_Product)) {
                return __('One of the additions was not found.', 'sage-front');
            }

            $additionCartItemKey = WC()->cart->add_to_cart(
                $additionId,
                $quantity,
            );

            if ($additionCartItemKey === false) {
                $notices = function_exists('wc_get_notices')
                    ? wc_get_notices('error')
                    : [];
                $message = self::extractErrorMessage($notices);

                return $message ?:
                    __(
                        'Unable to add one of the additions to cart.',
                        'sage-front',
                    );
            }

            $addedItemKeys[] = $additionCartItemKey;
        }

        return '';
    }

    /**
     * @param array<int, string> $addedItemKeys
     */
    protected static function rollbackCartItems(array $addedItemKeys): void
    {
        if (!(WC()->cart instanceof WC_Cart)) {
            return;
        }

        foreach ($addedItemKeys as $itemKey) {
            WC()->cart->remove_cart_item($itemKey);
        }
    }

    /**
     * @param array<int, array<string, mixed>>|mixed $notices
     */
    protected static function extractErrorMessage(mixed $notices): string
    {
        if (!is_array($notices)) {
            return '';
        }

        foreach ($notices as $notice) {
            if (!is_array($notice)) {
                continue;
            }

            $message = $notice['notice'] ?? '';

            if ($message instanceof WP_Error) {
                return $message->get_error_message();
            }

            if (is_string($message) && $message !== '') {
                return wp_strip_all_tags($message);
            }
        }

        return '';
    }
}
