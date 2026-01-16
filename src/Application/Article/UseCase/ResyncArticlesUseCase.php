<?php

declare(strict_types=1);

namespace App\Application\Article\UseCase;

use App\Domain\Article\Entity\Article;
use App\Domain\Article\Repository\ArticleRepositoryInterface;
use App\Domain\Article\ValueObject\ArticleId;
use App\Domain\Article\ValueObject\Content;
use App\Domain\Article\ValueObject\Description;
use App\Domain\Article\ValueObject\ImageUrl;
use App\Domain\Article\ValueObject\Language;
use App\Domain\Article\ValueObject\PublishedAt;
use App\Domain\Article\ValueObject\SourceName;
use App\Domain\Article\ValueObject\Title;
use App\Domain\Article\ValueObject\Url;
use App\Infrastructure\ExternalApi\GNews\GNewsApiClient;
use Psr\Log\LoggerInterface;

class ResyncArticlesUseCase
{
    private GNewsApiClient $gNewsApiClient;
    private ArticleRepositoryInterface $articleRepository;
    private LoggerInterface $logger;

    public function __construct(
        GNewsApiClient $gNewsApiClient,
        ArticleRepositoryInterface $articleRepository,
        LoggerInterface $logger
    ) {
        $this->gNewsApiClient = $gNewsApiClient;
        $this->articleRepository = $articleRepository;
        $this->logger = $logger;
    }

    /**
     * Resync articles from GNews API and update if content changed
     *
     * @param ?string $category
     * @param ?string $lang
     * @param int $max
     * @return array{updated: int, unchanged: int, new: int, total: int, pages: int}
     */
    public function execute(string|null $category = 'general', string|null $lang = 'en', int $max = 10): array
    {
        $this->logger->info('Resyncing articles from GNews API', [
            'category' => $category,
            'lang' => $lang,
            'max' => $max,
        ]);

        $updated = 0;
        $unchanged = 0;
        $new = 0;
        $totalArticles = 0;
        $page = 1;

        // Keep fetching pages until we get an empty page
        while (true) {
            $this->logger->info('Fetching page for resync', ['page' => $page]);

            try {
                $articles = $this->gNewsApiClient->getTopHeadlines($category, $lang, $max, $page);
            } catch (\Exception $e) {
                $this->logger->error('Failed to fetch articles from GNews API during resync', [
                    'page' => $page,
                    'error' => $e->getMessage(),
                ]);
                throw new \RuntimeException('Failed to fetch articles: ' . $e->getMessage(), 0, $e);
            }

            // If the page is empty, stop fetching
            if (empty($articles)) {
                $this->logger->info('Empty page received, stopping resync', ['page' => $page]);
                break;
            }

            $totalArticles += count($articles);

            foreach ($articles as $articleData) {
                try {
                    $existingArticle = $this->articleRepository->findByUrl($articleData['url']);

                    if ($existingArticle) {
                        // Article exists, check if content changed
                        $newArticle = $this->createArticleFromData($articleData, $articleData['lang'] ?? $lang);
                        
                        if ($this->hasContentChanged($existingArticle, $newArticle)) {
                            $this->articleRepository->update($newArticle);
                            $updated++;
                            $this->logger->info('Article updated', [
                                'url' => $articleData['url'],
                                'id' => $existingArticle->getId()->getValue()
                            ]);
                        } else {
                            $unchanged++;
                            $this->logger->debug('Article unchanged', ['url' => $articleData['url']]);
                        }
                    } else {
                        // New article, save it
                        $article = $this->createArticleFromData($articleData, $articleData['lang'] ?? $lang);
                        $this->articleRepository->save($article);
                        $new++;
                        $this->logger->info('New article saved', ['id' => $article->getId()->getValue()]);
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Failed to process article during resync', [
                        'article' => $articleData['title'] ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $page++;

            // Add 2 second delay to avoid rate limiting
            if (!empty($articles)) {
                $this->logger->debug('Waiting 2 seconds before next API call');
                sleep(2);
            }
        }

        $result = [
            'updated' => $updated,
            'unchanged' => $unchanged,
            'new' => $new,
            'total' => $totalArticles,
            'pages' => $page - 1,
        ];

        $this->logger->info('Articles resync completed', $result);

        return $result;
    }

    private function createArticleFromData(array $articleData, string $lang): Article
    {
        return new Article(
            new ArticleId(md5($articleData['url'])),
            new Title($articleData['title'] ?? 'No title'),
            new Description($articleData['description'] ?? 'No description'),
            new Content($articleData['content'] ?? 'No content'),
            new Url($articleData['url']),
            new ImageUrl($articleData['image'] ?? null),
            PublishedAt::fromString($articleData['publishedAt']),
            new SourceName($articleData['source']['name'] ?? 'Unknown source'),
            new Language($lang)
        );
    }

    private function hasContentChanged(Article $existing, Article $new): bool
    {
        return $existing->getTitle()->getValue() !== $new->getTitle()->getValue()
            || $existing->getDescription()->getValue() !== $new->getDescription()->getValue()
            || $existing->getContent()->getValue() !== $new->getContent()->getValue()
            || $existing->getImageUrl()->getValue() !== $new->getImageUrl()->getValue()
            || $existing->getPublishedAt()->format() !== $new->getPublishedAt()->format()
            || $existing->getSourceName()->getValue() !== $new->getSourceName()->getValue()
            || $existing->getLanguage()->getValue() !== $new->getLanguage()->getValue();
    }
}
