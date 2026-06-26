<?php

namespace App\Api;

use WP_REST_Request;
use WP_REST_Response;

class Categories
{
    protected const ROUTE_NAMESPACE = 'sage/v1';

    protected const ROUTE = '/categories';

    protected const ORDER_VALUES = ['ASC', 'DESC'];

    protected const ORDERBY_VALUES = [
        'name',
        'slug',
        'term_group',
        'term_id',
        'id',
        'description',
        'parent',
        'term_order',
        'include',
        'count',
        'meta_value',
        'meta_value_num',
        'none',
    ];

    protected const FIELDS_VALUES = [
        'all',
        'all_with_object_id',
        'ids',
        'tt_ids',
        'names',
        'slugs',
        'count',
        'id=>parent',
        'id=>name',
        'id=>slug',
    ];

    protected const GET_VALUES = ['all', 'all_with_object_id'];

    public static function boot(): void
    {
        add_action('rest_api_init', [self::class, 'registerRoutes']);
    }

    public static function registerRoutes(): void
    {
        register_rest_route(self::ROUTE_NAMESPACE, self::ROUTE, [
            'methods' => 'GET',
            'callback' => [self::class, 'handle'],
            'permission_callback' => [self::class, 'permission_callback'],
            'args' => [
                'taxonomy' => self::keyListArg(
                    'Taxonomy slug or list of taxonomy slugs.',
                ),
                'object_ids' => self::integerListArg(
                    'Limit results to terms attached to object IDs.',
                ),
                'orderby' => self::enumArg(
                    'Field used to sort terms.',
                    self::ORDERBY_VALUES,
                ),
                'order' => self::enumArg('Sort direction.', self::ORDER_VALUES),
                'hide_empty' => self::booleanArg(
                    'Whether to hide empty terms.',
                ),
                'include' => self::integerListArg(
                    'Include only terms with these IDs.',
                ),
                'exclude' => self::integerListArg(
                    'Exclude terms with these IDs.',
                ),
                'exclude_tree' => self::integerListArg(
                    'Exclude terms and their descendants by IDs.',
                ),
                'number' => self::integerArg(
                    'Maximum number of terms to return.',
                ),
                'offset' => self::integerArg(
                    'Number of terms to skip before collecting results.',
                ),
                'fields' => self::enumArg(
                    'Fields format returned by get_terms.',
                    self::FIELDS_VALUES,
                ),
                'name' => self::textListArg('Term name or list of term names.'),
                'slug' => self::textListArg('Term slug or list of term slugs.'),
                'term_taxonomy_id' => self::integerListArg(
                    'Filter by term taxonomy IDs.',
                ),
                'hierarchical' => self::booleanArg(
                    'Whether to include hierarchical descendants.',
                ),
                'search' => self::textArg('Search term names and slugs.'),
                'name__like' => self::textArg('Match terms by partial name.'),
                'description__like' => self::textArg(
                    'Match terms by partial description.',
                ),
                'pad_counts' => self::booleanArg(
                    'Whether to pad term counts in hierarchical taxonomies.',
                ),
                'get' => self::enumArg(
                    'Whether to return all terms regardless of hide_empty.',
                    self::GET_VALUES,
                ),
                'child_of' => self::integerArg(
                    'Return descendants of a given term ID.',
                ),
                'parent' => self::integerArg(
                    'Return direct children of a term ID.',
                ),
                'childless' => self::booleanArg(
                    'Whether to return only childless terms.',
                ),
                'cache_domain' => self::textArg(
                    'Custom cache domain for term queries.',
                ),
                'cache_results' => self::booleanArg(
                    'Whether to cache term query results.',
                ),
                'update_term_meta_cache' => self::booleanArg(
                    'Whether to prime term meta cache.',
                ),
                'meta_query' => self::arrayArg('Term meta query clauses.'),
                'meta_key' => self::textArg(
                    'Meta key used for filtering or sorting.',
                ),
                'meta_value' => self::textArg('Meta value used for filtering.'),
                'meta_type' => self::textArg(
                    'MySQL meta value type for comparisons.',
                ),
                'meta_compare' => self::textArg('Meta comparison operator.'),
            ],
        ]);
    }

    public static function handle(WP_REST_Request $request): WP_REST_Response
    {
        $queryArgs = self::requestToQueryArgs($request);
        $terms = get_terms($queryArgs);

        if (is_wp_error($terms)) {
            return new WP_REST_Response(
                [
                    'status' => 'error',
                    'message' => $terms->get_error_message(),
                    'code' => $terms->get_error_code(),
                ],
                400,
            );
        }

        return new WP_REST_Response(
            array_values(
                array_map(
                    static function ($term): array {
                        $link = get_term_link($term);

                        return [
                            'id' => (int) $term->term_id,
                            'name' => (string) $term->name,
                            'slug' => (string) $term->slug,
                            'taxonomy' => (string) $term->taxonomy,
                            'link' => is_string($link) ? $link : '',
                        ];
                    },
                    is_array($terms) ? $terms : [],
                ),
            ),
        );
    }

    public static function permission_callback(WP_REST_Request $request): bool
    {
        unset($request);

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function requestToQueryArgs(
        WP_REST_Request $request,
    ): array {
        $allowedKeys = [
            'taxonomy',
            'object_ids',
            'orderby',
            'order',
            'hide_empty',
            'include',
            'exclude',
            'exclude_tree',
            'number',
            'offset',
            'fields',
            'name',
            'slug',
            'term_taxonomy_id',
            'hierarchical',
            'search',
            'name__like',
            'description__like',
            'pad_counts',
            'get',
            'child_of',
            'parent',
            'childless',
            'cache_domain',
            'cache_results',
            'update_term_meta_cache',
            'meta_query',
            'meta_key',
            'meta_value',
            'meta_type',
            'meta_compare',
        ];

        $queryArgs = [];

        foreach ($allowedKeys as $key) {
            if (!$request->has_param($key)) {
                continue;
            }

            $queryArgs[$key] = $request->get_param($key);
        }

        return $queryArgs;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function booleanArg(string $description): array
    {
        return [
            'description' => $description,
            'sanitize_callback' => static function ($value): bool {
                return rest_sanitize_boolean($value);
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function integerArg(string $description): array
    {
        return [
            'description' => $description,
            'sanitize_callback' => static function ($value): int {
                return absint($value);
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function integerListArg(string $description): array
    {
        return [
            'description' => $description,
            'sanitize_callback' => static function ($value): array {
                $items = is_array($value) ? $value : [$value];

                return array_values(
                    array_filter(
                        array_map('absint', $items),
                        static fn(int $item): bool => $item > 0,
                    ),
                );
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function textArg(string $description): array
    {
        return [
            'description' => $description,
            'sanitize_callback' => static function ($value): string {
                return sanitize_text_field((string) $value);
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function textListArg(string $description): array
    {
        return [
            'description' => $description,
            'sanitize_callback' => static function ($value): array|string {
                if (!is_array($value)) {
                    return sanitize_text_field((string) $value);
                }

                return array_values(
                    array_filter(
                        array_map(
                            static fn($item): string => sanitize_text_field(
                                (string) $item,
                            ),
                            $value,
                        ),
                        static fn(string $item): bool => $item !== '',
                    ),
                );
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function keyListArg(string $description): array
    {
        return [
            'description' => $description,
            'sanitize_callback' => static function ($value): array|string {
                if (!is_array($value)) {
                    return sanitize_key((string) $value);
                }

                return array_values(
                    array_filter(
                        array_map(
                            static fn($item): string => sanitize_key(
                                (string) $item,
                            ),
                            $value,
                        ),
                        static fn(string $item): bool => $item !== '',
                    ),
                );
            },
        ];
    }

    /**
     * @param array<int, string> $allowedValues
     * @return array<string, mixed>
     */
    protected static function enumArg(
        string $description,
        array $allowedValues,
    ): array {
        return [
            'description' => $description,
            'sanitize_callback' => static function ($value) use (
                $allowedValues,
            ): string {
                $sanitized = sanitize_text_field((string) $value);
                $normalizedAllowed = array_map('strval', $allowedValues);

                if (in_array($sanitized, $normalizedAllowed, true)) {
                    return $sanitized;
                }

                $upperSanitized = strtoupper($sanitized);

                if (in_array($upperSanitized, $normalizedAllowed, true)) {
                    return $upperSanitized;
                }

                return '';
            },
            'validate_callback' => static function ($value) use (
                $allowedValues,
            ): bool {
                return in_array((string) $value, $allowedValues, true)
                    || in_array(strtoupper((string) $value), $allowedValues, true);
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected static function arrayArg(string $description): array
    {
        return [
            'description' => $description,
            'sanitize_callback' => static function ($value): array {
                return is_array($value) ? $value : [];
            },
        ];
    }
}
