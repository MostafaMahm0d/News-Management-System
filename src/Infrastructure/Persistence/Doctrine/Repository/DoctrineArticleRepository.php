<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

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
use App\Infrastructure\Persistence\Doctrine\Entity\ArticleEntity;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineArticleRepository implements ArticleRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Save a new article to the database
     * 
     * @param Article $article The article entity to save
     * @return void
     */
    public function save(Article $article): void
    {
        
        $articleEntity = new ArticleEntity(
            $article->getId()->getValue(),
            $article->getTitle()->getValue(),
            $article->getDescription()->getValue(),
            $article->getContent()->getValue(),
            $article->getUrl()->getValue(),
            $article->getImageUrl()->getValue(),
            $article->getPublishedAt()->getValue(),
            $article->getSourceName()->getValue(),
            $article->getLanguage()->getValue()
        );

        $this->entityManager->persist($articleEntity);
        $this->entityManager->flush();
    }

    /**
     * Find an article by its ID
     * 
     * @param ArticleId $id The article ID value object
     * @return Article|null Returns the article if found, null otherwise
     */
    public function findById(ArticleId $id): ?Article
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        $articleEntity = $repository->find($id->getValue());

        if (!$articleEntity) {
            return null;
        }

        return $this->toDomain($articleEntity);
    }

    /**
     * Find all articles with pagination
     * 
     * @param int $limit Maximum number of articles to return (default: 100)
     * @param int $offset Number of articles to skip (default: 0)
     * @return Article[] Array of article entities
     */
    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        $articleEntities = $repository->findBy([], ['publishedAt' => 'DESC'], $limit, $offset);

        return array_map(fn(ArticleEntity $entity) => $this->toDomain($entity), $articleEntities);
    }

    /**
     * Find articles with filters and sorting
     * 
     * @param array<string, mixed> $filters Associative array of filters (e.g., ['language' => 'en'])
     * @param string $orderBy Field to sort by: 'publishedAt', 'createdAt', 'updatedAt', 'title' (default: 'publishedAt')
     * @param string $orderDirection Sort direction: 'ASC' or 'DESC' (default: 'DESC')
     * @param int $limit Maximum number of articles to return (default: 100)
     * @param int $offset Number of articles to skip for pagination (default: 0)
     * @return Article[] Array of article entities matching the filters
     */
    public function findWithFilters(
        array $filters = [],
        string $orderBy = 'publishedAt',
        string $orderDirection = 'DESC',
        int $limit = 100,
        int $offset = 0
    ): array {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
            ->from(ArticleEntity::class, 'a');

        // Apply filters
        if (!empty($filters['language'])) {
            $qb->andWhere('a.language = :language')
                ->setParameter('language', $filters['language']);
        }

        // Apply sorting
        $allowedOrderBy = ['publishedAt', 'createdAt', 'updatedAt', 'title'];
        if (in_array($orderBy, $allowedOrderBy)) {
            $orderDirection = strtoupper($orderDirection) === 'ASC' ? 'ASC' : 'DESC';
            $qb->orderBy('a.' . $orderBy, $orderDirection);
        } else {
            $qb->orderBy('a.publishedAt', 'DESC');
        }

        $qb->setMaxResults($limit)
            ->setFirstResult($offset);

        $articleEntities = $qb->getQuery()->getResult();

        return array_map(fn(ArticleEntity $entity) => $this->toDomain($entity), $articleEntities);
    }

    /**
     * Count articles matching the given filters
     * 
     * @param array<string, mixed> $filters Associative array of filters (e.g., ['language' => 'en'])
     * @return int Total number of articles matching the filters
     */
    public function countWithFilters(array $filters = []): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(a.id)')
            ->from(ArticleEntity::class, 'a');

        // Apply filters
        if (!empty($filters['language'])) {
            $qb->andWhere('a.language = :language')
                ->setParameter('language', $filters['language']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Check if an article exists by its URL
     * 
     * @param string $url The article URL to check
     * @return bool True if article exists, false otherwise
     */
    public function existsByUrl(string $url): bool
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        $count = $repository->count(['url' => $url]);

        return $count > 0;
    }

    /**
     * Find an article by its URL
     * 
     * @param string $url The article URL to search for
     * @return Article|null Returns the article if found, null otherwise
     */
    public function findByUrl(string $url): ?Article
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        $articleEntity = $repository->findOneBy(['url' => $url]);

        if (!$articleEntity) {
            return null;
        }

        return $this->toDomain($articleEntity);
    }

    /**
     * Update an existing article in the database
     * 
     * @param Article $article The article entity with updated data
     * @return void
     * @throws \RuntimeException If article is not found
     */
    public function update(Article $article): void
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        $articleEntity = $repository->find($article->getId()->getValue());

        if (!$articleEntity) {
            throw new \RuntimeException('Article not found for update');
        }

        // Update the entity using reflection to set private properties
        $reflection = new \ReflectionClass($articleEntity);
        
        $titleProp = $reflection->getProperty('title');
        $titleProp->setAccessible(true);
        $titleProp->setValue($articleEntity, $article->getTitle()->getValue());
        
        $descriptionProp = $reflection->getProperty('description');
        $descriptionProp->setAccessible(true);
        $descriptionProp->setValue($articleEntity, $article->getDescription()->getValue());
        
        $contentProp = $reflection->getProperty('content');
        $contentProp->setAccessible(true);
        $contentProp->setValue($articleEntity, $article->getContent()->getValue());
        
        $imageUrlProp = $reflection->getProperty('imageUrl');
        $imageUrlProp->setAccessible(true);
        $imageUrlProp->setValue($articleEntity, $article->getImageUrl()->getValue());
        
        $publishedAtProp = $reflection->getProperty('publishedAt');
        $publishedAtProp->setAccessible(true);
        $publishedAtProp->setValue($articleEntity, $article->getPublishedAt()->getValue());
        
        $sourceNameProp = $reflection->getProperty('sourceName');
        $sourceNameProp->setAccessible(true);
        $sourceNameProp->setValue($articleEntity, $article->getSourceName()->getValue());
        
        $languageProp = $reflection->getProperty('language');
        $languageProp->setAccessible(true);
        $languageProp->setValue($articleEntity, $article->getLanguage()->getValue());

        // Trigger the PreUpdate lifecycle callback
        $articleEntity->setUpdatedAt();

        $this->entityManager->flush();
    }

    /**
     * Count total number of articles in the database
     * 
     * @return int Total number of articles
     */
    public function count(): int
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        return $repository->count([]);
    }

    private function toDomain(ArticleEntity $entity): Article
    {
        return new Article(
            new ArticleId($entity->getId()),
            new Title($entity->getTitle()),
            new Description($entity->getDescription()),
            new Content($entity->getContent()),
            new Url($entity->getUrl()),
            new ImageUrl($entity->getImageUrl()),
            new PublishedAt($entity->getPublishedAt()),
            new SourceName($entity->getSourceName()),
            new Language($entity->getLanguage())
        );
    }
}
