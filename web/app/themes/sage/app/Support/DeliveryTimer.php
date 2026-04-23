<?php

namespace App\Support;

use DateTimeImmutable;

class DeliveryTimer
{
    public const OPTION_NAME = 'sage_delivery_timer_settings';

    /**
     * @var array<string, string>|null
     */
    protected ?array $settings = null;

    public static function boot(): void
    {
        add_shortcode('delivery_timer', [self::class, 'renderShortcode']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function renderShortcode(array $attributes = []): string
    {
        unset($attributes);

        return view('partials.delivery-timer', [
            'deliveryTimer' => app(self::class)->viewData(),
        ])->render();
    }

    /**
     * @return array<string, string>
     */
    public static function defaultOptions(): array
    {
        return [
            'weekday_hours' => '9-17',
            'weekend_hours' => '9-14',
            'holidays' => '',
        ];
    }

    /**
     * @param mixed $input
     * @return array<string, string>
     */
    public function sanitizeOptions(mixed $input): array
    {
        $input = is_array($input) ? $input : [];

        return [
            'weekday_hours' => $this->sanitizeHourRange(
                (string) ($input['weekday_hours'] ??
                    self::defaultOptions()['weekday_hours']),
                self::defaultOptions()['weekday_hours'],
            ),
            'weekend_hours' => $this->sanitizeHourRange(
                (string) ($input['weekend_hours'] ??
                    self::defaultOptions()['weekend_hours']),
                self::defaultOptions()['weekend_hours'],
            ),
            'holidays' => implode(
                ', ',
                $this->normalizeHolidayList($input['holidays'] ?? ''),
            ),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function settings(): array
    {
        $stored = get_option(self::OPTION_NAME, []);

        return $this->settings ??= array_replace(
            self::defaultOptions(),
            $this->sanitizeOptions($stored),
        );
    }

    /**
     * @return array{
     *     timezone: string,
     *     weekdayHours: array{start: int, end: int},
     *     weekendHours: array{start: int, end: int},
     *     holidays: array<int, string>
     * }
     */
    public function viewData(): array
    {
        $settings = $this->settings();

        return [
            'timezone' => $this->timezone(),
            'weekdayHours' => $this->parseHourRange($settings['weekday_hours']),
            'weekendHours' => $this->parseHourRange($settings['weekend_hours']),
            'holidays' => $this->normalizeHolidayList($settings['holidays']),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function workingHoursDetails(): array
    {
        $settings = $this->settings();

        return [
            sprintf(
                'pn-pt: %s',
                $this->formatHourRange($settings['weekday_hours']),
            ),
            sprintf(
                'sob-nd: %s',
                $this->formatHourRange($settings['weekend_hours']),
            ),
        ];
    }

    protected function timezone(): string
    {
        $timezone = get_option('timezone_string');

        return is_string($timezone) && $timezone !== ''
            ? $timezone
            : 'Europe/Warsaw';
    }

    protected function sanitizeHourRange(
        string $value,
        string $fallback,
    ): string {
        $parsed = $this->parseHourRange($value);

        if ($parsed === null) {
            return $fallback;
        }

        return sprintf('%d-%d', $parsed['start'], $parsed['end']);
    }

    /**
     * @return array{start: int, end: int}|null
     */
    protected function parseHourRange(string $value): ?array
    {
        if (
            !preg_match('/^\s*(\d{1,2})\s*-\s*(\d{1,2})\s*$/', $value, $matches)
        ) {
            return null;
        }

        $start = (int) $matches[1];
        $end = (int) $matches[2];

        if (
            $start < 0 ||
            $start > 23 ||
            $end < 1 ||
            $end > 24 ||
            $start >= $end
        ) {
            return null;
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    protected function formatHourRange(string $value): string
    {
        $hours = $this->parseHourRange($value);

        if ($hours === null) {
            return $value;
        }

        return sprintf('%02d:00-%02d:00', $hours['start'], $hours['end']);
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    protected function normalizeHolidayList(mixed $value): array
    {
        $value = is_array($value) ? implode(',', $value) : (string) $value;
        $parts = preg_split('/[\s,]+/', $value) ?: [];
        $holidays = [];

        foreach ($parts as $part) {
            $date = trim($part);

            if ($date === '') {
                continue;
            }

            $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

            if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
                continue;
            }

            $holidays[] = $date;
        }

        $holidays = array_values(array_unique($holidays));
        sort($holidays);

        return $holidays;
    }
}
