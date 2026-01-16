<?php

declare(strict_types=1);

namespace App\Presentation\Controller;


use App\Application\Article\UseCase\GetArticleByIdUseCase;
use App\Application\Article\UseCase\GetArticleListUseCase;
use App\Domain\Article\Exception\ArticleNotFoundException;
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

        $articleList = $getArticleListUseCase->execute($limit, $offset, $filters, $orderBy, $orderDirection);
        $totalCount = $getArticleListUseCase->getTotalCount($filters);

        return $this->json([
            'data' => $articleList,
            'meta' => [
                'total' => $totalCount,
                'limit' => $limit,
                'offset' => $offset,
                'filters' => $filters,
                'orderBy' => $orderBy,
                'orderDirection' => $orderDirection,
            ],
        ]);
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
        try {
            $article = $getArticleByIdUseCase->execute($id);

            return $this->json([
                'success' => true,
                'data' => $article,
            ]);
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


    
}
