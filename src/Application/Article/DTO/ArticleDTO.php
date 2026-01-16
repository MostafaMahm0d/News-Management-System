<?php

declare(strict_types=1);

namespace App\Application\Article\DTO;

class ArticleDTO implements \JsonSerializable
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

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'url' => $this->url,
            'imageUrl' => $this->imageUrl,
            'publishedAt' => $this->publishedAt,
            'sourceName' => $this->sourceName,
            'language' => $this->language,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
