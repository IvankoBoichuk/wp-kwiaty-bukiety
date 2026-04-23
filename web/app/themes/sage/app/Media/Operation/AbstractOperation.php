<?php

namespace App\Media\Operation;

use App\Media\Image;
use App\Media\Operation\InterfaceOperation;

abstract class AbstractOperation implements InterfaceOperation
{
    protected function sourcePath(Image $image): ?string
    {
        $path = $image->path();

        return is_string($path) && $path !== '' && is_file($path) ? $path : null;
    }

    protected function sourceUrl(Image $image): string
    {
        return $image->src();
    }

    protected function buildTargetPath(Image $image, string $signature, ?string $extension = null): ?string
    {
        $sourcePath = $this->sourcePath($image);

        if ($sourcePath === null) {
            return null;
        }

        $info = pathinfo($sourcePath);
        $targetExtension = ltrim(strtolower((string) ($extension ?? ($info['extension'] ?? ''))), '.');

        if ($targetExtension === '') {
            return null;
        }

        $filename = (string) ($info['filename'] ?? 'image');
        $directory = (string) ($info['dirname'] ?? '');

        return $directory.DIRECTORY_SEPARATOR.$filename.'--'.$signature.'.'.$targetExtension;
    }

    protected function targetUrl(Image $image, string $targetPath): string
    {
        $sourceUrl = $this->sourceUrl($image);

        return trailingslashit(dirname($sourceUrl)).basename($targetPath);
    }

    protected function existingTargetUrl(Image $image, ?string $targetPath): ?string
    {
        if (! is_string($targetPath) || $targetPath === '' || ! file_exists($targetPath)) {
            return null;
        }

        return $this->targetUrl($image, $targetPath);
    }

    protected function editor(Image $image): \WP_Image_Editor|\WP_Error
    {
        $sourcePath = $this->sourcePath($image);

        return $sourcePath !== null ? wp_get_image_editor($sourcePath) : new \WP_Error('missing_source', 'Missing source image.');
    }

    protected function applyQuality(\WP_Image_Editor $editor, ?int $quality): void
    {
        if ($quality !== null) {
            $editor->set_quality($quality);
        }
    }

    protected function saveEditor(Image $image, \WP_Image_Editor $editor, string $targetPath, ?string $mimeType = null): string
    {
        $saved = $editor->save($targetPath, $mimeType);

        if (is_wp_error($saved)) {
            return $image->src();
        }

        return $this->targetUrl($image, $targetPath);
    }

    protected function openRaster(string $path)
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        return @imagecreatefromstring($contents);
    }

    protected function saveRaster($resource, string $path, string $extension, int $quality = 82): bool
    {
        $extension = strtolower(ltrim($extension, '.'));

        return match ($extension) {
            'jpg', 'jpeg' => imagejpeg($resource, $path, $quality),
            'png' => imagepng($resource, $path, max(0, min(9, (int) round((100 - $quality) / 10)))),
            'gif' => imagegif($resource, $path),
            'webp' => function_exists('imagewebp') ? imagewebp($resource, $path, $quality) : false,
            default => false,
        };
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    protected function hexToRgb(string $hex): array
    {
        $value = preg_replace('/[^0-9a-f]/i', '', $hex) ?: 'ffffff';

        if (strlen($value) === 3) {
            $value = preg_replace('/(.)/', '$1$1', $value) ?: 'ffffff';
        }

        $value = str_pad(substr($value, 0, 6), 6, 'f');

        return [
            hexdec(substr($value, 0, 2)),
            hexdec(substr($value, 2, 2)),
            hexdec(substr($value, 4, 2)),
        ];
    }
}