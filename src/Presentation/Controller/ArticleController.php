<?php

declare(strict_types=1);

namespace App\Presentation\Controller;


use App\Application\Article\UseCase\GetArticleByIdUseCase;
use App\Application\Article\UseCase\GetArticleListUseCase;
use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Service\RedisCacheService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/articles', name: 'api_articles_')]
#[OA\Tag(name: 'Articles')]
class ArticleController extends AbstractController
{
    public function __construct(
        private RedisCacheService $cache
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/articles',
        summary: 'Get list of articles',
        description: 'Returns a paginated list of articles with filtering and sorting options'
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Number of articles to return',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 20)
    )]
    #[OA\Parameter(
        name: 'offset',
        in: 'query',
        description: 'Number of articles to skip',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 0)
    )]
    #[OA\Parameter(
        name: 'language',
        in: 'query',
        description: 'Filter by language',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'orderBy',
        in: 'query',
        description: 'Field to order by',
        required: false,
        schema: new OA\Schema(type: 'string', default: 'publishedAt')
    )]
    #[OA\Parameter(
        name: 'orderDirection',
        in: 'query',
        description: 'Order direction',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['ASC', 'DESC'], default: 'DESC')
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                new OA\Property(
                    property: 'meta',
                    properties: [
                        new OA\Property(property: 'total', type: 'integer'),
                        new OA\Property(property: 'limit', type: 'integer'),
                        new OA\Property(property: 'offset', type: 'integer'),
                        new OA\Property(property: 'filters', type: 'object'),
                        new OA\Property(property: 'orderBy', type: 'string'),
                        new OA\Property(property: 'orderDirection', type: 'string')
                    ],
                    type: 'object'
                )
            ]
        )
    )]
    public function list(Request $request, GetArticleListUseCase $getArticleListUseCase): JsonResponse
    {
        $limit = (int) $request->query->get('limit', 20);
        $offset = (int) $request->query->get('offset', 0);
        $language = $request->query->get('language');
        $orderBy = $request->query->get('orderBy', 'publishedAt');
        $orderDirection = $request->query->get('orderDirection', 'DESC');

        // Build filters
        $filters = [];
        if ($language) {
            $filters['language'] = $language;
        }

        // Create cache key based on request parameters
        $cacheKey = sprintf('articles_list_%d_%d_%s_%s_%s', $limit, $offset, $language ?? 'all', $orderBy, $orderDirection);


        // Try to get from cache
        $cachedData = $this->cache->get($cacheKey);
        
        if ($cachedData !== null) {
            // Return cached response
            return $this->json($cachedData);
        }

        // Cache miss - fetch from database
        $articleList = $getArticleListUseCase->execute($limit, $offset, $filters, $orderBy, $orderDirection);
        $totalCount = $getArticleListUseCase->getTotalCount($filters);

        $responseData = [
            'data' => $articleList,
            'meta' => [
                'total' => $totalCount,
                'limit' => $limit,
                'offset' => $offset,
                'filters' => $filters,
                'orderBy' => $orderBy,
                'orderDirection' => $orderDirection,
                'cached' => false,
            ],
        ];

        // Store in cache for 5 minutes (300 seconds)
        $this->cache->set($cacheKey, $responseData, 300);

        return $this->json($responseData);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/articles/{id}',
        summary: 'Get article by ID',
        description: 'Returns a single article by its ID'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Article ID',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'object')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Article not found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string', example: 'Article not found')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Internal server error',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: false),
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function show(string $id, GetArticleByIdUseCase $getArticleByIdUseCase): JsonResponse
    {
        // Create cache key for single article
        $cacheKey = 'article_' . $id;

        // Try to get from cache
        $cachedData = $this->cache->get($cacheKey);
        
        if ($cachedData !== null) {
            // Return cached response
            return $this->json($cachedData);
        }

        try {
            $article = $getArticleByIdUseCase->execute($id);

            $responseData = [
                'success' => true,
                'data' => $article,
                'cached' => false,
            ];

            // Store in cache for 10 minutes (600 seconds)
            $this->cache->set($cacheKey, $responseData, 600);

            return $this->json($responseData);
        } catch (ArticleNotFoundException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to retrieve article: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/cache/clear', name: 'cache_clear', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/articles/cache/clear',
        summary: 'Clear articles cache',
        description: 'Clears all cached article data'
    )]
    #[OA\Response(
        response: 200,
        description: 'Cache cleared successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Cache cleared successfully')
            ]
        )
    )]
    public function clearCache(): JsonResponse
    {
        $result = $this->cache->clear();
        
        return $this->json([
            'success' => $result,
            'message' => $result ? 'Cache cleared successfully' : 'Failed to clear cache',
        ]);
    }

    #[Route('/cache/test', name: 'cache_test', methods: ['GET'])]
    #[OA\Get(
        path: '/api/articles/cache/test',
        summary: 'Test Redis connection',
        description: 'Tests if Redis cache is working properly'
    )]
    #[OA\Response(
        response: 200,
        description: 'Cache test result',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'timestamp', type: 'string')
            ]
        )
    )]
    public function testCache(): JsonResponse
    {
        $isWorking = $this->cache->testConnection();
        
        return $this->json([
            'success' => $isWorking,
            'message' => $isWorking ? 'Redis cache is working!' : 'Redis cache connection failed',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }


    
}
