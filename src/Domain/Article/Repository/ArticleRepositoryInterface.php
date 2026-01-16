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

    /**
     * Find articles with filters and sorting
     * 
     * @param array $filters ['language' => 'en']
     * @param string $orderBy 'publishedAt'
     * @param string $orderDirection 'ASC' or 'DESC'
     * @param int $limit
     * @param int $offset
     * @return Article[]
     */
    public function findWithFilters(
        array $filters = [],
        string $orderBy = 'publishedAt',
        string $orderDirection = 'DESC',
        int $limit = 100,
        int $offset = 0
    ): array;

    /**
     * Count articles with filters
     * 
     * @param array $filters ['language' => 'en']
     * @return int
     */
    public function countWithFilters(array $filters = []): int;

    public function existsByUrl(string $url): bool;

    public function findByUrl(string $url): ?Article;

    public function update(Article $article): void;

    public function count(): int;
}
