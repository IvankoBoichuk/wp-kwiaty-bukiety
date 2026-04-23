<?php

namespace App\Admin;

use App\Support\DeliveryTimer;

class DeliveryTimerSettingsPage
{
    protected const PAGE_SLUG = 'sage-delivery-timer-settings';

    public static function boot(): void
    {
        add_action('admin_menu', [self::class, 'registerPage']);
        add_action('admin_init', [self::class, 'registerSettings']);
    }

    public static function registerPage(): void
    {
        add_theme_page(
            __('Delivery Timer', 'sage'),
            __('Delivery Timer', 'sage'),
            'manage_options',
            self::PAGE_SLUG,
            [self::class, 'renderPage'],
        );
    }

    public static function registerSettings(): void
    {
        register_setting(
            'sage_delivery_timer_settings',
            DeliveryTimer::OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [self::class, 'sanitizeOptions'],
                'default' => DeliveryTimer::defaultOptions(),
                'show_in_rest' => false,
            ],
        );
    }

    /**
     * @param mixed $input
     * @return array<string, string>
     */
    public static function sanitizeOptions(mixed $input): array
    {
        return app(DeliveryTimer::class)->sanitizeOptions($input);
    }

    public static function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $options = app(DeliveryTimer::class)->settings();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__(
                'Налаштування таймера доставки',
                'sage',
            ); ?></h1>
            <p class="description">
                <?php echo esc_html__(
                    'Задайте години роботи для буднів, вихідних і перелік додаткових вихідних днів.',
                    'sage',
                ); ?>
            </p>

            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php settings_fields('sage_delivery_timer_settings'); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="sage-delivery-timer-weekday-hours">
                                    <?php echo esc_html__(
                                        'Робочі години у будні',
                                        'sage',
                                    ); ?>
                                </label>
                            </th>
                            <td>
                                <input
                                    id="sage-delivery-timer-weekday-hours"
                                    name="<?php echo esc_attr(
                                        DeliveryTimer::OPTION_NAME,
                                    ); ?>[weekday_hours]"
                                    type="text"
                                    class="regular-text"
                                    value="<?php echo esc_attr(
                                        $options['weekday_hours'],
                                    ); ?>"
                                />
                                <p class="description">
                                    <?php echo esc_html__(
                                        'Формат: 9-17',
                                        'sage',
                                    ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sage-delivery-timer-weekend-hours">
                                    <?php echo esc_html__(
                                        'Робочі години у вихідні',
                                        'sage',
                                    ); ?>
                                </label>
                            </th>
                            <td>
                                <input
                                    id="sage-delivery-timer-weekend-hours"
                                    name="<?php echo esc_attr(
                                        DeliveryTimer::OPTION_NAME,
                                    ); ?>[weekend_hours]"
                                    type="text"
                                    class="regular-text"
                                    value="<?php echo esc_attr(
                                        $options['weekend_hours'],
                                    ); ?>"
                                />
                                <p class="description">
                                    <?php echo esc_html__(
                                        'Формат: 9-14. Застосовується в суботу та неділю.',
                                        'sage',
                                    ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sage-delivery-timer-holidays">
                                    <?php echo esc_html__(
                                        'Додаткові вихідні',
                                        'sage',
                                    ); ?>
                                </label>
                            </th>
                            <td>
                                <textarea
                                    id="sage-delivery-timer-holidays"
                                    name="<?php echo esc_attr(
                                        DeliveryTimer::OPTION_NAME,
                                    ); ?>[holidays]"
                                    rows="4"
                                    class="large-text"
                                ><?php echo esc_textarea(
                                    $options['holidays'],
                                ); ?></textarea>
                                <p class="description">
                                    <?php echo esc_html__(
                                        'Формат: YYYY-MM-DD, через кому.',
                                        'sage',
                                    ); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(__('Зберегти', 'sage')); ?>
            </form>
        </div>
        <?php
    }
}
