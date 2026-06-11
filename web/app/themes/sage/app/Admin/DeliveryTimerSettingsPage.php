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
            __('Delivery Timer', 'sage-back'),
            __('Delivery Timer', 'sage-back'),
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
                'Delivery timer settings',
                'sage-back',
            ); ?></h1>
            <p class="description">
                <?php echo esc_html__(
                    'Set the working hours, delivery time slots, and the list of additional holidays.',
                    'sage-back',
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
                                        'Working hours on weekdays',
                                        'sage-back',
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
                                        'Format: 9-17',
                                        'sage-back',
                                    ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sage-delivery-timer-weekend-hours">
                                    <?php echo esc_html__(
                                        'Working hours on weekends',
                                        'sage-back',
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
                                        'Format: 9-14. Applies on Saturday and Sunday.',
                                        'sage-back',
                                    ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sage-delivery-timer-time-slots">
                                    <?php echo esc_html__(
                                        'Delivery time slots',
                                        'sage-back',
                                    ); ?>
                                </label>
                            </th>
                            <td>
                                <textarea
                                    id="sage-delivery-timer-time-slots"
                                    name="<?php echo esc_attr(
                                        DeliveryTimer::OPTION_NAME,
                                    ); ?>[time_slots]"
                                    rows="4"
                                    class="large-text"
                                ><?php echo esc_textarea(
                                    $options['time_slots'],
                                ); ?></textarea>
                                <p class="description">
                                    <?php echo esc_html__(
                                        'Format: 08-12, 12-15, 15-18, 18-21. You can separate values by commas or new lines.',
                                        'sage-back',
                                    ); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="sage-delivery-timer-holidays">
                                    <?php echo esc_html__(
                                        'Additional holidays',
                                        'sage-back',
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
                                        'Format: YYYY-MM-DD, separated by commas.',
                                        'sage-back',
                                    ); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(__('Save', 'sage-back')); ?>
            </form>
        </div>
        <?php
    }
}
