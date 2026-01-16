<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'articles')]
#[ORM\Index(columns: ['url'], name: 'idx_articles_url')]
#[ORM\Index(columns: ['published_at'], name: 'idx_articles_published_at')]
#[ORM\Index(columns: ['language'], name: 'idx_articles_language')]
#[ORM\HasLifecycleCallbacks]
class ArticleEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private string $id;

    #[ORM\Column(type: 'string', length: 500)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'string', length: 500, unique: true)]
    private string $url;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $imageUrl;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $publishedAt;

    #[ORM\Column(type: 'string', length: 255)]
    private string $sourceName;

    #[ORM\Column(type: 'string', length: 10)]
    private string $language;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $title,
        string $description,
        string $content,
        string $url,
        ?string $imageUrl,
        \DateTimeImmutable $publishedAt,
        string $sourceName,
        string $language
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->content = $content;
        $this->url = $url;
        $this->imageUrl = $imageUrl;
        $this->publishedAt = $publishedAt;
        $this->sourceName = $sourceName;
        $this->language = $language;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
