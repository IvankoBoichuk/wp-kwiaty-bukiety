<?php

namespace App\Media;

use App\Media\Operation\Letterbox;
use App\Media\Operation\Resize;
use App\Media\Operation\Retina;
use App\Media\Operation\ToJpg;
use App\Media\Operation\ToWebp;

final class Image
{
    private ?int $bytes = null;

    private bool $bytesResolved = false;

    private ?string $fileExtension = null;

    private ?ImageDimensions $dimensions = null;

    public function __construct(
        public readonly int $attachmentId,
        public readonly string $size = 'medium',
        public readonly string $fallbackAlt = '',
        public readonly string $fallbackSrc = '/images/placeholder.jpg',
    ) {}

    public static function fromAttachmentId(
        int $attachmentId,
        string $size = 'medium',
        string $fallbackAlt = '',
        string $fallbackSrc = '/images/placeholder.jpg',
    ): self {
        return new self(
            attachmentId: absint($attachmentId),
            size: $size,
            fallbackAlt: $fallbackAlt,
            fallbackSrc: $fallbackSrc,
        );
    }
    public static function fromUrl(
        string $url,
        string $size = 'medium',
        string $fallbackAlt = '',
        string $fallbackSrc = '/images/placeholder.jpg',
    ): self {
        $attachmentId = attachment_url_to_postid($url);
        return self::fromAttachmentId(
            $attachmentId,
            $size,
            $fallbackAlt,
            $fallbackSrc,
        );
    }

    public function src(): string
    {
        return (string) (wp_get_attachment_image_url(
            $this->attachmentId,
            $this->size,
        )
        ?: $this->fallbackSrc);
    }

    public function alt(): string
    {
        return (string) (get_post_meta(
            $this->attachmentId,
            '_wp_attachment_image_alt',
            true,
        )
        ?: $this->fallbackAlt);
    }

    public function width(): ?int
    {
        return $this->dimensions()->width();
    }

    public function height(): ?int
    {
        return $this->dimensions()->height();
    }

    public function aspect(): ?float
    {
        return $this->dimensions()->aspect();
    }

    public function srcset(): string
    {
        return (string) (wp_get_attachment_image_srcset(
            $this->attachmentId,
            $this->size,
        )
        ?: '');
    }

    public function sizes(): string
    {
        return (string) (wp_get_attachment_image_sizes(
            $this->attachmentId,
            $this->size,
        )
        ?: '');
    }

    public function size(): ?int
    {
        if ($this->bytesResolved) {
            return $this->bytes;
        }

        $size = $this->metadata('filesize');

        if ($size !== null && is_numeric($size)) {
            $this->bytes = (int) $size;
            $this->bytesResolved = true;

            return $this->bytes;
        }

        $file = $this->fileLoc();
        $size = $file !== null ? filesize($file) : false;

        $this->bytes = $size === false ? null : (int) $size;
        $this->bytesResolved = true;

        return $this->bytes;
    }

    public function extension(): string
    {
        if ($this->fileExtension !== null) {
            return $this->fileExtension;
        }

        return $this->fileExtension = (string) pathinfo(
            $this->fileLoc() ?? '',
            PATHINFO_EXTENSION,
        );
    }

    public function path(): ?string
    {
        return $this->fileLoc();
    }

    public function resize(
        ?int $width,
        ?int $height = null,
        bool $crop = false,
        ?int $quality = null,
    ): string {
        $resize = new Resize($width, $height, $crop, $quality);
        return $resize->apply($this);
    }

    public function letterbox(
        int $width,
        int $height,
        string $background = 'ffffff',
        int $quality = 82,
    ): string {
        $letterbox = new Letterbox($width, $height, $background, $quality);
        return $letterbox->apply($this);
    }

    public function retina(
        int $width,
        ?int $height = null,
        bool $crop = false,
        ?int $quality = null,
    ): string {
        $retina = new Retina($width, $height, $crop, $quality);
        return $retina->apply($this);
    }

    public function toJpg(?int $quality = 90): string
    {
        $toJpg = new ToJpg($quality);
        return $toJpg->apply($this);
    }

    public function toWebp(?int $quality = 82): string
    {
        $toWebp = new ToWebp($quality);
        return $toWebp->apply($this);
    }

    protected function metadata(?string $key = null): mixed
    {
        $metadata = wp_get_attachment_metadata($this->attachmentId);

        if (!is_array($metadata)) {
            return null;
        }

        if ($key === null) {
            return $metadata;
        }

        return $metadata[$key] ?? null;
    }

    protected function fileLoc(): ?string
    {
        $path = get_attached_file($this->attachmentId);

        if (!is_string($path) || $path === '') {
            return null;
        }

        return $path;
    }

    protected function dimensions(): ImageDimensions
    {
        if ($this->dimensions !== null) {
            return $this->dimensions;
        }

        return $this->dimensions = new ImageDimensions(
            $this->fileLoc(),
            $this->attachmentId,
        );
    }
}
