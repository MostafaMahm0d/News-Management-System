<?php

declare(strict_types=1);

namespace App\Presentation\Controller;


use App\Application\Article\UseCase\GetArticleByIdUseCase;
use App\Application\Article\UseCase\GetArticleListUseCase;
use App\Domain\Article\Exception\ArticleNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/articles', name: 'api_articles_')]
class ArticleController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
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


    // #[Route('/resync', name: 'resync', methods: ['POST'])]
    // public function resync(Request $request, ResyncArticlesUseCase $resyncArticlesUseCase): JsonResponse
    // {
    //     $data = json_decode($request->getContent(), true);

    //     $category = $data['category'] ?? 'general';
    //     $lang = $data['lang'] ?? 'en';
    //     $max = $data['max'] ?? 10;

    //     try {
    //         $result = $resyncArticlesUseCase->execute($category, $lang, $max);

    //         return $this->json([
    //             'success' => true,
    //             'message' => 'Articles resynced successfully',
    //             'data' => $result,
    //         ]);
    //     } catch (\Exception $e) {
    //         return $this->json([
    //             'success' => false,
    //             'message' => 'Failed to resync articles: ' . $e->getMessage(),
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
}
