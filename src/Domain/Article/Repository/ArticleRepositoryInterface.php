<?php

declare(strict_types=1);

namespace App\Domain\Article\Repository;

use App\Domain\Article\Entity\Article;
use App\Domain\Article\ValueObject\ArticleId;

interface ArticleRepositoryInterface
{
    public function save(Article $article): void;

    public function findById(ArticleId $id): ?Article;

    /**
     * @return Article[]
     */
    public function findAll(int $limit = 100, int $offset = 0): array;

    public function existsByUrl(string $url): bool;

    public function findByUrl(string $url): ?Article;

    public function update(Article $article): void;

    public function count(): int;
}
