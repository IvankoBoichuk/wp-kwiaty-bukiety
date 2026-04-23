<?php

namespace App\Api;

use WP_REST_Request;
use WP_REST_Response;

class Healthcheck
{
    protected const ROUTE_NAMESPACE = 'sage/v1';

    protected const ROUTE = '/healthcheck';

    public static function boot(): void
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes(): void
    {
        register_rest_route(self::ROUTE_NAMESPACE, self::ROUTE, [
            'methods' => 'GET',
            'callback' => [self::class, 'handle'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle(WP_REST_Request $request): WP_REST_Response
    {
        unset($request);

        return new WP_REST_Response([
            'status' => 'ok',
            'service' => wp_get_theme()->get('Name') ?: 'sage',
            'environment' => wp_get_environment_type(),
            'timestamp' => gmdate('c'),
        ]);
    }
}
