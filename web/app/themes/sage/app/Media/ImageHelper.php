<?php

namespace App\Media;

final class ImageHelper
{
    public static function boot(): bool
    {
        add_action('delete_attachment', [self::class, 'deleteAttachment']);
        add_filter('wp_generate_attachment_metadata', [self::class, 'generateAttachmentMetadata'], 10, 2);

        return true;
    }

    public static function resize(int|string|Image $source, int|string $width, int $height = 0, bool $crop = false): string
    {
        $image = self::image($source);

        if (! $image instanceof Image) {
            return self::sourceString($source);
        }

        [$resolvedWidth, $resolvedHeight] = self::resolveDimensions($width, $height);

        if ($resolvedWidth === null && $resolvedHeight === null) {
            return $image->src();
        }

        return $image->resize($resolvedWidth, $resolvedHeight, $crop);
    }

    public static function retinaResize(int|string|Image $source, float $multiplier = 2): string
    {
        $image = self::image($source);

        if (! $image instanceof Image) {
            return self::sourceString($source);
        }

        $width = $image->width();
        $height = $image->height();

        if ($width === null || $width <= 0) {
            return $image->src();
        }

        $retinaWidth = max(1, (int) round($width * $multiplier));
        $retinaHeight = $height !== null && $height > 0
            ? max(1, (int) round($height * $multiplier))
            : null;

        return $image->resize($retinaWidth, $retinaHeight);
    }

    public static function letterbox(int|string|Image $source, int $width, int $height, string|bool $color = false): string
    {
        $image = self::image($source);

        if (! $image instanceof Image) {
            return self::sourceString($source);
        }

        $background = is_string($color) && $color !== '' ? $color : 'ffffff';

        return $image->letterbox($width, $height, $background);
    }

    public static function imgToJpg(int|string|Image $source, string $background = 'FFFFFF'): string
    {
        $image = self::image($source);

        if (! $image instanceof Image) {
            return self::sourceString($source);
        }

        return $image->toJpg();
    }

    public static function imgToWebp(int|string|Image $source, int $quality = 80): string
    {
        $image = self::image($source);

        if (! $image instanceof Image) {
            return self::sourceString($source);
        }

        return $image->toWebp($quality);
    }

    public static function deleteAttachment(int $attachmentId): void
    {
        self::deleteGeneratedFiles($attachmentId);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public static function generateAttachmentMetadata(array $metadata, int $attachmentId): array
    {
        self::deleteGeneratedFiles($attachmentId);

        return $metadata;
    }

    public static function deleteGeneratedFiles(int $attachmentId): void
    {
        $path = get_attached_file($attachmentId);

        if (! is_string($path) || $path === '' || ! file_exists($path)) {
            return;
        }

        $info = pathinfo($path);
        $directory = (string) ($info['dirname'] ?? '');
        $filename = (string) ($info['filename'] ?? '');

        if ($directory === '' || $filename === '') {
            return;
        }

        $pattern = $directory . DIRECTORY_SEPARATOR . $filename . '--*';
        $files = glob($pattern);

        if ($files === false) {
            return;
        }

        foreach ($files as $generatedFile) {
            if (! is_string($generatedFile) || ! is_file($generatedFile)) {
                continue;
            }

            @unlink($generatedFile);
        }
    }

    /**
     * @return array{0:?int,1:?int}
     */
    protected static function resolveDimensions(int|string $width, int $height): array
    {
        if (is_string($width) && ! is_numeric($width)) {
            $dimensions = self::findWordPressDimensions($width);

            if ($dimensions === null) {
                return [null, null];
            }

            return [$dimensions['width'], $dimensions['height']];
        }

        $resolvedWidth = is_numeric($width) ? (int) $width : null;
        $resolvedHeight = $height > 0 ? $height : null;

        return [
            $resolvedWidth !== null && $resolvedWidth > 0 ? $resolvedWidth : null,
            $resolvedHeight,
        ];
    }

    /**
     * @return array{width:int,height:int}|null
     */
    protected static function findWordPressDimensions(string $size): ?array
    {
        global $_wp_additional_image_sizes;

        if (isset($_wp_additional_image_sizes[$size])) {
            return [
                'width' => absint($_wp_additional_image_sizes[$size]['width'] ?? 0),
                'height' => absint($_wp_additional_image_sizes[$size]['height'] ?? 0),
            ];
        }

        if (in_array($size, ['thumbnail', 'medium', 'large'], true)) {
            return [
                'width' => absint(get_option($size . '_size_w')),
                'height' => absint(get_option($size . '_size_h')),
            ];
        }

        return null;
    }

    protected static function image(int|string|Image $source): ?Image
    {
        if ($source instanceof Image) {
            return $source;
        }

        $attachmentId = self::resolveAttachmentId($source);

        return $attachmentId > 0 ? Image::fromAttachmentId($attachmentId) : null;
    }

    protected static function resolveAttachmentId(int|string|Image $source): int
    {
        if ($source instanceof Image) {
            return $source->attachmentId;
        }

        if (is_int($source) || ctype_digit((string) $source)) {
            return absint($source);
        }

        $value = trim((string) $source);

        if ($value === '') {
            return 0;
        }

        if (str_starts_with($value, ABSPATH) || preg_match('/^[A-Za-z]:\\\\/', $value) === 1) {
            return self::resolveAttachmentIdFromPath($value);
        }

        if (str_starts_with($value, '/')) {
            return self::resolveAttachmentIdFromUrl(home_url($value));
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return self::resolveAttachmentIdFromUrl($value);
        }

        return 0;
    }

    protected static function resolveAttachmentIdFromUrl(string $url): int
    {
        return absint(attachment_url_to_postid($url));
    }

    protected static function resolveAttachmentIdFromPath(string $path): int
    {
        $normalized = wp_normalize_path($path);
        $uploads = wp_get_upload_dir();
        $baseDir = wp_normalize_path((string) ($uploads['basedir'] ?? ''));
        $baseUrl = (string) ($uploads['baseurl'] ?? '');

        if ($baseDir === '' || ! str_starts_with($normalized, $baseDir)) {
            return 0;
        }

        $relative = ltrim(substr($normalized, strlen($baseDir)), '/');

        if ($relative === '' || $baseUrl === '') {
            return 0;
        }

        return self::resolveAttachmentIdFromUrl(trailingslashit($baseUrl) . $relative);
    }

    protected static function sourceString(int|string|Image $source): string
    {
        if ($source instanceof Image) {
            return $source->src();
        }

        return (string) $source;
    }
}
