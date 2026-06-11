<?php

namespace App\Admin;

class ContactSettingsPage
{
    protected const OPTION_NAME = 'sage_contact_settings';

    protected const PAGE_SLUG = 'sage-contact-settings';

    /**
     * @var array<string, string>
     */
    protected const SOCIAL_NETWORKS = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'telegram' => 'Telegram',
        'whatsapp' => 'WhatsApp',
        'youtube' => 'YouTube',
        'linkedin' => 'LinkedIn',
        'x' => 'X',
        'pinterest' => 'Pinterest',
    ];

    protected static string $pageHook = '';

    public static function boot(): void
    {
        add_action('admin_menu', [self::class, 'registerPage']);
        add_action('admin_init', [self::class, 'registerSettings']);
    }

    public static function registerPage(): void
    {
        self::$pageHook = (string) add_theme_page(
            __('Contact Settings', 'sage-back'),
            __('Contacts', 'sage-back'),
            'manage_options',
            self::PAGE_SLUG,
            [self::class, 'renderPage'],
        );

        if (self::$pageHook === '') {
            return;
        }

        add_action('admin_head-' . self::$pageHook, [
            self::class,
            'renderStyles',
        ]);
        add_action('admin_footer-' . self::$pageHook, [
            self::class,
            'renderScripts',
        ]);
    }

    public static function registerSettings(): void
    {
        register_setting('sage_contact_settings', self::OPTION_NAME, [
            'type' => 'array',
            'sanitize_callback' => [self::class, 'sanitizeOptions'],
            'default' => self::defaultOptions(),
            'show_in_rest' => false,
        ]);
    }

    /**
     * @param mixed $input
     * @return array<string, array<int, array<string, string>>>
     */
    public static function sanitizeOptions(mixed $input): array
    {
        $input = is_array($input) ? $input : [];

        $phones = array_values(
            array_filter(
                array_map(static function ($item): ?array {
                    if (!is_array($item)) {
                        return null;
                    }

                    $label = sanitize_text_field($item['label'] ?? '');
                    $number = preg_replace(
                        '/[^0-9+\s\-()]/',
                        '',
                        (string) ($item['number'] ?? ''),
                    );
                    $number = trim((string) $number);

                    if ($number === '') {
                        return null;
                    }

                    return [
                        'label' => $label,
                        'number' => $number,
                    ];
                }, self::listItems($input['phones'] ?? [])),
            ),
        );

        $emails = array_values(
            array_filter(
                array_map(static function ($item): ?array {
                    if (!is_array($item)) {
                        return null;
                    }

                    $label = sanitize_text_field($item['label'] ?? '');
                    $email = sanitize_email($item['email'] ?? '');

                    if ($email === '') {
                        return null;
                    }

                    return [
                        'label' => $label,
                        'email' => $email,
                    ];
                }, self::listItems($input['emails'] ?? [])),
            ),
        );

        $socials = array_values(
            array_filter(
                array_map(static function ($item): ?array {
                    if (!is_array($item)) {
                        return null;
                    }

                    $network = sanitize_key($item['network'] ?? '');
                    $url = esc_url_raw($item['url'] ?? '');

                    if (
                        $url === '' ||
                        !array_key_exists($network, self::SOCIAL_NETWORKS)
                    ) {
                        return null;
                    }

                    return [
                        'network' => $network,
                        'url' => $url,
                    ];
                }, self::listItems($input['socials'] ?? [])),
            ),
        );

        return [
            'phones' => $phones,
            'emails' => $emails,
            'socials' => $socials,
        ];
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    public static function getOptions(): array
    {
        $stored = get_option(self::OPTION_NAME, []);

        return array_replace_recursive(
            self::defaultOptions(),
            is_array($stored) ? $stored : [],
        );
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function phones(): array
    {
        return self::getOptions()['phones'];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function emails(): array
    {
        return self::getOptions()['emails'];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function socials(): array
    {
        return array_map(function (array $item): array {
            $item['label'] = self::SOCIAL_NETWORKS[$item['network']];
            return $item;
        }, self::getOptions()['socials']);
    }

    /**
     * @return array<string, string>
     */
    public static function socialNetworks(): array
    {
        return self::SOCIAL_NETWORKS;
    }

    public static function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $options = self::getOptions();
        ?>
        <div class="wrap sage-contact-settings">
            <h1><?php echo esc_html__('Contact Settings', 'sage-back'); ?></h1>
            <p class="description">
                <?php echo esc_html__(
                    'Manage phones, emails, and social network links from one page.',
                    'sage-back',
                ); ?>
            </p>

            <form method="post" action="options.php">
                <?php settings_fields('sage_contact_settings'); ?>

                <div class="sage-contact-settings__grid">
                    <?php
                    self::renderRepeater(
                        'phones',
                        __('Phones', 'sage-back'),
                        __(
                            'Each item contains a label and phone number.',
                            'sage-back',
                        ),
                        [
                            [
                                'key' => 'label',
                                'label' => __('Label', 'sage-back'),
                                'type' => 'text',
                                'placeholder' => __(
                                    'Sales department',
                                    'sage-back',
                                ),
                            ],
                            [
                                'key' => 'number',
                                'label' => __('Phone number', 'sage-back'),
                                'type' => 'text',
                                'placeholder' => __(
                                    '+48 123 456 789',
                                    'sage-back',
                                ),
                            ],
                        ],
                        $options['phones'],
                    );

                    self::renderRepeater(
                        'emails',
                        __('Emails', 'sage-back'),
                        __(
                            'Each item contains a label and email address.',
                            'sage-back',
                        ),
                        [
                            [
                                'key' => 'label',
                                'label' => __('Label', 'sage-back'),
                                'type' => 'text',
                                'placeholder' => __(
                                    'Customer support',
                                    'sage-back',
                                ),
                            ],
                            [
                                'key' => 'email',
                                'label' => __('Email', 'sage-back'),
                                'type' => 'email',
                                'placeholder' => __(
                                    'hello@example.com',
                                    'sage-back',
                                ),
                            ],
                        ],
                        $options['emails'],
                    );

                    self::renderRepeater(
                        'socials',
                        __('Social Networks', 'sage-back'),
                        __('Select the network and set its URL.', 'sage-back'),
                        [
                            [
                                'key' => 'network',
                                'label' => __('Network', 'sage-back'),
                                'type' => 'select',
                                'options' => self::SOCIAL_NETWORKS,
                            ],
                            [
                                'key' => 'url',
                                'label' => __('Link', 'sage-back'),
                                'type' => 'url',
                                'placeholder' => __(
                                    'https://instagram.com/your-profile',
                                    'sage-back',
                                ),
                            ],
                        ],
                        $options['socials'],
                    );
                    ?>
                </div>

                <div class="sage-contact-settings__submit">
                    <?php submit_button(__('Save changes', 'sage-back')); ?>
                </div>
            </form>
        </div>
        <?php
    }

    public static function renderStyles(): void
    {
        ?>
        <style>
            .sage-contact-settings {
                max-width: 1120px;
            }

            .sage-contact-settings__grid {
                display: grid;
                gap: 24px;
                margin-top: 24px;
            }

            .sage-settings-card {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 12px;
                box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
                overflow: hidden;
            }

            .sage-settings-card__header {
                border-bottom: 1px solid #f0f0f1;
                padding: 20px 24px 16px;
            }

            .sage-settings-card__header h2 {
                font-size: 16px;
                line-height: 1.4;
                margin: 0 0 4px;
            }

            .sage-settings-card__header p {
                color: #50575e;
                margin: 0;
            }

            .sage-settings-card__body {
                padding: 24px;
            }

            .sage-repeater {
                display: grid;
                gap: 16px;
            }

            .sage-repeater__items {
                display: grid;
                gap: 16px;
            }

            .sage-repeater__item {
                background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
                border: 1px solid #dcdcde;
                border-radius: 10px;
                padding: 16px;
            }

            .sage-repeater__item-header {
                align-items: center;
                display: flex;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 12px;
            }

            .sage-repeater__item-title {
                color: #1d2327;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: 0.02em;
                margin: 0;
                text-transform: uppercase;
            }

            .sage-repeater__fields {
                display: grid;
                gap: 16px;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            }

            .sage-repeater__field {
                display: grid;
                gap: 6px;
            }

            .sage-repeater__field label {
                color: #1d2327;
                font-size: 13px;
                font-weight: 500;
            }

            .sage-repeater__field input,
            .sage-repeater__field select {
                border-radius: 8px;
                min-height: 40px;
            }

            .sage-repeater__actions {
                display: flex;
                justify-content: flex-start;
            }

            .sage-contact-settings__submit {
                margin-top: 24px;
            }
        </style>
        <?php
    }

    public static function renderScripts(): void
    {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-repeater]').forEach(function (repeater) {
                    const items = repeater.querySelector('[data-repeater-items]');
                    const template = repeater.querySelector('template');
                    const addButton = repeater.querySelector('[data-repeater-add]');

                    if (!items || !template || !addButton) {
                        return;
                    }

                    const nextIndex = function () {
                        const indices = Array.from(items.children)
                            .map(function (item) {
                                return Number(item.getAttribute('data-index'));
                            })
                            .filter(function (value) {
                                return !Number.isNaN(value);
                            });

                        return indices.length ? Math.max.apply(null, indices) + 1 : 0;
                    };

                    addButton.addEventListener('click', function () {
                        items.insertAdjacentHTML(
                            'beforeend',
                            template.innerHTML.replace(/__index__/g, String(nextIndex())),
                        );
                    });

                    items.addEventListener('click', function (event) {
                        const button = event.target.closest('[data-repeater-remove]');

                        if (!button) {
                            return;
                        }

                        const item = button.closest('[data-repeater-item]');

                        if (item) {
                            item.remove();
                        }
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @param array<int, array<string, string>> $items
     */
    protected static function renderRepeater(
        string $key,
        string $title,
        string $description,
        array $fields,
        array $items,
    ): void {
        $items = $items !== [] ? $items : [self::emptyItem($fields)]; ?>
        <section class="sage-settings-card">
            <div class="sage-settings-card__header">
                <h2><?php echo esc_html($title); ?></h2>
                <p><?php echo esc_html($description); ?></p>
            </div>

            <div class="sage-settings-card__body">
                <div class="sage-repeater" data-repeater>
                    <div class="sage-repeater__items" data-repeater-items>
                        <?php foreach (
                            array_values($items)
                            as $index => $item
                        ): ?>
                            <?php self::renderRepeaterItem(
                                $key,
                                $index,
                                $fields,
                                $item,
                            ); ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="sage-repeater__actions">
                        <button
                            type="button"
                            class="button button-secondary"
                            data-repeater-add
                        >
                            <?php echo esc_html__('Add item', 'sage-back'); ?>
                        </button>
                    </div>

                    <template>
                        <?php self::renderRepeaterItem(
                            $key,
                            '__index__',
                            $fields,
                            self::emptyItem($fields),
                        ); ?>
                    </template>
                </div>
            </div>
        </section>
        <?php
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, string> $item
     */
    protected static function renderRepeaterItem(
        string $group,
        int|string $index,
        array $fields,
        array $item,
    ): void {
        ?>
        <div class="sage-repeater__item" data-repeater-item data-index="<?php echo esc_attr(
            (string) $index,
        ); ?>">
            <div class="sage-repeater__item-header">
                <p class="sage-repeater__item-title">
                    <?php echo esc_html__('Item', 'sage-back'); ?>
                </p>

                <button type="button" class="button-link-delete" data-repeater-remove>
                    <?php echo esc_html__('Remove', 'sage-back'); ?>
                </button>
            </div>

            <div class="sage-repeater__fields">
                <?php foreach ($fields as $field): ?>
                    <?php
                    $fieldKey = (string) $field['key'];
                    $fieldType = (string) $field['type'];
                    $fieldName = sprintf(
                        '%s[%s][%s][%s]',
                        self::OPTION_NAME,
                        $group,
                        $index,
                        $fieldKey,
                    );
                    $fieldValue = (string) ($item[$fieldKey] ?? '');
                    ?>

                    <div class="sage-repeater__field">
                        <label><?php echo esc_html(
                            (string) $field['label'],
                        ); ?></label>

                        <?php if ($fieldType === 'select'): ?>
                            <select name="<?php echo esc_attr($fieldName); ?>">
                                <?php foreach (
                                    (array) ($field['options'] ?? [])
                                    as $value => $label
                                ): ?>
                                    <option value="<?php echo esc_attr(
                                        (string) $value,
                                    ); ?>" <?php selected(
    $fieldValue,
    (string) $value,
); ?>>
                                        <?php echo esc_html((string) $label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input
                                type="<?php echo esc_attr($fieldType); ?>"
                                name="<?php echo esc_attr($fieldName); ?>"
                                value="<?php echo esc_attr($fieldValue); ?>"
                                placeholder="<?php echo esc_attr(
                                    (string) ($field['placeholder'] ?? ''),
                                ); ?>"
                            />
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, string>
     */
    protected static function emptyItem(array $fields): array
    {
        $item = [];

        foreach ($fields as $field) {
            $item[(string) $field['key']] = (string) ($field['default'] ?? '');
        }

        return $item;
    }

    /**
     * @param mixed $items
     * @return array<int, mixed>
     */
    protected static function listItems(mixed $items): array
    {
        return is_array($items) ? array_values($items) : [];
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    protected static function defaultOptions(): array
    {
        return [
            'phones' => [],
            'emails' => [],
            'socials' => [],
        ];
    }
}
