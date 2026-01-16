<?php

declare(strict_types=1);

namespace App\Application\Article\UseCase;

use App\Application\Article\DTO\ArticleDTO;
use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\Article\ValueObject\ArticleId;

class GetArticleByIdUseCase
{
    private ArticleRepositoryInterface $articleRepository;

    public function __construct(ArticleRepositoryInterface $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * Get a single article by its ID
     *
     * @param string $id
     * @return ArticleDTO
     * @throws ArticleNotFoundException
     */
    public function execute(string $id): ArticleDTO
    {
        $articleId = new ArticleId($id);
        $article = $this->articleRepository->findById($articleId);

        if (!$article) {
            throw new ArticleNotFoundException(sprintf('Article with ID "%s" not found', $id));
        }

        return new ArticleDTO(
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
        );
    }
}
