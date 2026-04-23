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
		public readonly string $text,
	) {}

	public static function      fromWordPressComment(WP_Comment $comment): self
	{
		$location = (string) get_comment_meta($comment->comment_ID, 'location', true);
		$rating = (float) get_comment_meta($comment->comment_ID, 'rating', true);

		return new self(
			reviewId: (int) $comment->comment_ID,
			name: (string) ($comment->comment_author ?? ''),
			location: $location,
			rating: $rating,
			text: (string) ($comment->comment_content ?? ''),
		);
	}

	/**
	 * @return array<string, int|float|string>
	 */
	public function toArray(): array
	{
		return [
			'reviewId' => $this->reviewId,
			'name' => $this->name,
			'location' => $this->location,
			'rating' => $this->rating,
			'text' => $this->text,
		];
	}
}
