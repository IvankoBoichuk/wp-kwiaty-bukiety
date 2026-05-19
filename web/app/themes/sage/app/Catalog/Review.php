<?php

namespace App\Catalog;

use WP_Comment;

final class Review
{
    public function __construct(
        public readonly int $reviewId,
        public readonly string $name,
        public readonly string $location,
        public readonly float $rating,
        public readonly int $fullStars,
        public readonly string $text,
    ) {}

    public static function fromWordPressComment(WP_Comment $comment): self
    {
        $location = (string) get_comment_meta(
            $comment->comment_ID,
            'location',
            true,
        );
        $rating =
            (float) (get_comment_meta($comment->comment_ID, 'rating', true) ??
                0);

        return new self(
            reviewId: (int) $comment->comment_ID,
            name: (string) ($comment->comment_author ?? ''),
            location: $location,
            rating: $rating,
            fullStars: (int) floor($rating),
            text: (string) ($comment->comment_content ?? ''),
        );
    }
}
