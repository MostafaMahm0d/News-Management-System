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

    public function findById(ArticleId $id): ?Article
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        $articleEntity = $repository->find($id->getValue());

        if (!$articleEntity) {
            return null;
        }

        return $this->toDomain($articleEntity);
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        $articleEntities = $repository->findBy([], ['publishedAt' => 'DESC'], $limit, $offset);

        return array_map(fn(ArticleEntity $entity) => $this->toDomain($entity), $articleEntities);
    }

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

    public function existsByUrl(string $url): bool
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        $count = $repository->count(['url' => $url]);

        return $count > 0;
    }

    public function findByUrl(string $url): ?Article
    {
        $repository = $this->entityManager->getRepository(ArticleEntity::class);
        $articleEntity = $repository->findOneBy(['url' => $url]);

        if (!$articleEntity) {
            return null;
        }

        return $this->toDomain($articleEntity);
    }

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
