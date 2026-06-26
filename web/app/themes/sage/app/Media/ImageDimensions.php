<?php

namespace App\Media;

final class ImageDimensions
{
    /**
     * @var array{0:int,1:int}|null
     */
    private ?array $dimensions = null;

    public function __construct(
        public readonly ?string $filePath = null,
        public readonly ?int $attachmentId = null,
    ) {}

    public function width(): ?int
    {
        return $this->dimension('width');
    }

    public function height(): ?int
    {
        return $this->dimension('height');
    }

    public function aspect(): ?float
    {
        $width = $this->width();
        $height = $this->height();

        if ($width === null || $height === null || $height <= 0) {
            return null;
        }

        return $width / $height;
    }

    public function orientation(): ?string
    {
        $width = $this->width();
        $height = $this->height();

        if ($width === null || $height === null || $width <= 0 || $height <= 0) {
            return null;
        }

        if ($width === $height) {
            return 'square';
        }

        return $width > $height ? 'landscape' : 'portrait';
    }

    public function dimension(string $name): ?int
    {
        if ($this->dimensions === null) {
            $this->dimensions = $this->loadDimensions();
        }

        if ($this->dimensions === null) {
            return null;
        }

        return in_array(strtolower($name), ['h', 'height'], true)
            ? $this->dimensions[1]
            : $this->dimensions[0];
    }

    /**
     * @return array{0:int,1:int}|null
     */
    private function loadDimensions(): ?array
    {
        $fromMetadata = $this->fromMetadata();

        if ($fromMetadata !== null) {
            return $fromMetadata;
        }

        $path = $this->filePath;

        if (! is_string($path) || $path === '' || ! is_file($path)) {
            return null;
        }

        $fileSize = filesize($path);

        if ($fileSize === false || $fileSize === 0) {
            return null;
        }

        if (strtolower((string) pathinfo($path, PATHINFO_EXTENSION)) === 'svg') {
            return $this->fromSvg($path);
        }

        $imageSize = getimagesize($path);

        if (! is_array($imageSize) || ! isset($imageSize[0], $imageSize[1])) {
            return null;
        }

        return [absint($imageSize[0]), absint($imageSize[1])];
    }

    /**
     * @return array{0:int,1:int}|null
     */
    private function fromMetadata(): ?array
    {
        if (($this->attachmentId ?? 0) <= 0) {
            return null;
        }

        $metadata = wp_get_attachment_metadata($this->attachmentId);

        if (! is_array($metadata) || ! isset($metadata['width'], $metadata['height'])) {
            return null;
        }

        return [absint($metadata['width']), absint($metadata['height'])];
    }

    /**
     * @return array{0:int,1:int}|null
     */
    private function fromSvg(string $path): ?array
    {
        $svg = simplexml_load_file($path);

        if ($svg === false) {
            return null;
        }

        $attributes = $svg->attributes();

        if (isset($attributes->viewBox)) {
            $parts = preg_split('/\s+/', trim((string) $attributes->viewBox)) ?: [];

            if (isset($parts[2], $parts[3])) {
                return [absint((int) round((float) $parts[2])), absint((int) round((float) $parts[3]))];
            }
        }

        if (isset($attributes->width, $attributes->height)) {
            return [
                absint((int) round((float) $attributes->width)),
                absint((int) round((float) $attributes->height)),
            ];
        }

        return null;
    }
}
