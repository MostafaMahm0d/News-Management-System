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

class FetchArticlesUseCase
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
     * Fetch articles from GNews API and save to database
     *
     * @param ?string $category
     * @param ?string $lang
     * @param int $max
     * @return array{saved: int, skipped: int, total: int, pages: int}
     */
    public function execute(string|null $category = 'general', string|null $lang = 'en', int $max = 10): array
    {
        $this->logger->info('Fetching articles from GNews API', [
            'category' => $category,
            'lang' => $lang,
            'max' => $max,
        ]);

        $saved = 0;
        $skipped = 0;
        $totalArticles = 0;
        $page = 1;

        // Keep fetching pages until we get an empty page
        while (true) {
            $this->logger->info('Fetching page', ['page' => $page]);

            try {
                $articles = $this->gNewsApiClient->getTopHeadlines($category, $lang, $max, $page);
            } catch (\Exception $e) {
                $this->logger->error('Failed to fetch articles from GNews API', [
                    'page' => $page,
                    'error' => $e->getMessage(),
                ]);
                throw new \RuntimeException('Failed to fetch articles: ' . $e->getMessage(), 0, $e);
            }

            // If the page is empty, stop fetching
            if (empty($articles)) {
                $this->logger->info('Empty page received, stopping', ['page' => $page]);
                break;
            }

            $totalArticles += count($articles);

            foreach ($articles as $articleData) {
                try {
                    // Skip if already exists
                    if ($this->articleRepository->existsByUrl($articleData['url'])) {
                        $skipped++;
                        $this->logger->debug('Skipping duplicate article', ['url' => $articleData['url']]);
                        continue;
                    }

                    $article = $this->createArticleFromData($articleData, $articleData['lang'] ?? $lang);
                    $this->articleRepository->save($article);
                    $saved++;

                    $this->logger->info('Article saved successfully', ['id' => $article->getId()->getValue()]);
                } catch (\Exception $e) {
                    $this->logger->error('Failed to save article', [
                        'article' => $articleData,
                        'error' => $e->getMessage(),
                    ]);
                    $skipped++;
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
            'saved' => $saved,
            'skipped' => $skipped,
            'total' => $totalArticles,
            'pages' => $page - 1, // Subtract 1 because we incremented after the last successful page
        ];

        $this->logger->info('Articles fetch completed', $result);

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
}
