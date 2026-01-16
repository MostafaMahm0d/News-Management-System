<?php

declare(strict_types=1);

namespace App\Application\Article\DTO;

class ArticleDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $content,
        public readonly string $url,
        public readonly ?string $imageUrl,
        public readonly string $publishedAt,
        public readonly string $sourceName,
        public readonly string $language,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }
}
