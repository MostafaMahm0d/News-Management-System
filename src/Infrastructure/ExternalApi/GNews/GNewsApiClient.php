<?php

declare(strict_types=1);

namespace App\Infrastructure\ExternalApi\GNews;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GNewsApiClient
{
    private HttpClientInterface $httpClient;
    private string $apiKey;
    private string $baseUrl = 'https://gnews.io/api/v4';
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, string $apiKey, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    /**
     * Fetch top headlines from GNews API
     *
     * @param string|null $category Category (general, world, nation, business, technology, entertainment, sports, science, health)
     * @param string|null $lang Language code (en, ar, etc.)
     * @param int $max Maximum number of articles (default: 10, max: 100)
     * @param int $page Page number (1-based)
     * @return array
     */
    public function getTopHeadlines(string|null $category = 'general', string|null $lang = 'en', int $max = 10, int $page = 1): array
    {
        $this->logger->info('GNews API: Fetching top headlines', [
            'category' => $category,
            'lang' => $lang,
            'max' => $max,
            'page' => $page,
        ]);

        $startTime = microtime(true);

        try {
            $response = $this->httpClient->request('GET', $this->baseUrl . '/top-headlines', [
                'query' => array_filter([
                    'category' => $category,
                    'lang' => $lang,
                    'max' => $max,
                    'page' => $page,
                    'apikey' => $this->apiKey,
                    
                ], fn($value) => $value !== null),
            ]);

            $data = $response->toArray();
            $articles = $data['articles'] ?? [];

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->info('GNews API: Top headlines fetched successfully', [
                'articles_count' => count($articles),
                'page' => $page,
                'duration_ms' => $duration,
            ]);

            return $articles;
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->error('GNews API: Failed to fetch top headlines', [
                'category' => $category,
                'lang' => $lang,
                'page' => $page,
                'duration_ms' => $duration,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Search for articles by query
     *
     * @param string|null $query Search query
     * @param string|null $lang Language code
     * @param int $max Maximum number of articles
     * @param int $page Page number (1-based)
     * @return array
     */
    public function search(string|null $query, string|null $lang = 'en', int $max = 10, int $page = 1): array
    {
        $this->logger->info('GNews API: Searching articles', [
            'query' => $query,
            'lang' => $lang,
            'max' => $max,
            'page' => $page,
        ]);

        $startTime = microtime(true);

        try {
            $response = $this->httpClient->request('GET', $this->baseUrl . '/search', [
                'query' => array_filter([
                    'q' => $query,
                    'lang' => $lang,
                    'max' => $max,
                    'page' => $page,
                    'apikey' => $this->apiKey,
                ], fn($value) => $value !== null),
            ]);

            $data = $response->toArray();
            $articles = $data['articles'] ?? [];

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->info('GNews API: Search completed successfully', [
                'query' => $query,
                'articles_count' => count($articles),
                'page' => $page,
                'duration_ms' => $duration,
            ]);

            return $articles;
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            $this->logger->error('GNews API: Search failed', [
                'query' => $query,
                'lang' => $lang,
                'page' => $page,
                'duration_ms' => $duration,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
