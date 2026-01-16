<?php

declare(strict_types=1);

namespace App\Application\Article\UseCase;

use App\Application\Article\DTO\ArticleDTO;
use App\Domain\Article\Entity\Article;
use App\Domain\Article\Repository\ArticleRepositoryInterface;

class GetArticleListUseCase
{
    private ArticleRepositoryInterface $articleRepository;

    public function __construct(ArticleRepositoryInterface $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * Get articles with optional filters and sorting
     * 
     * @param int $limit
     * @param int $offset
     * @param array $filters ['language' => 'en']
     * @param string $orderBy 'publishedAt', 'createdAt', 'updatedAt', 'title'
     * @param string $orderDirection 'ASC' or 'DESC'
     * @return ArticleDTO[]
     */
    public function execute(
        int $limit = 20,
        int $offset = 0,
        array $filters = [],
        string $orderBy = 'publishedAt',
        string $orderDirection = 'DESC'
    ): array {
        $articleList = $this->articleRepository->findWithFilters(
            $filters,
            $orderBy,
            $orderDirection,
            $limit,
            $offset
        );

        return array_map(
            fn(Article $article) => new ArticleDTO(
                $article->getId()->getValue(),
                $article->getTitle()->getValue(),
                $article->getDescription()->getValue(),
                $article->getContent()->getValue(),
                $article->getUrl()->getValue(),
                $article->getImageUrl()->getValue(),
                $article->getPublishedAt()->format(),
                $article->getSourceName()->getValue(),
                $article->getLanguage()->getValue(),
                $article->getCreatedAt()->format('Y-m-d H:i:s'),
                $article->getUpdatedAt()->format('Y-m-d H:i:s')
            ),
            $articleList
        );
    }

    public function getTotalCount(array $filters = []): int
    {
        return $this->articleRepository->countWithFilters($filters);
    }
}
