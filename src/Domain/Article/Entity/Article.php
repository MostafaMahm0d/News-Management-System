<?php

declare(strict_types=1);

namespace App\Domain\Article\Entity;

use App\Domain\Article\ValueObject\ArticleId;
use App\Domain\Article\ValueObject\Content;
use App\Domain\Article\ValueObject\Description;
use App\Domain\Article\ValueObject\ImageUrl;
use App\Domain\Article\ValueObject\Language;
use App\Domain\Article\ValueObject\PublishedAt;
use App\Domain\Article\ValueObject\SourceName;
use App\Domain\Article\ValueObject\Title;
use App\Domain\Article\ValueObject\Url;
use DateTimeImmutable;

class Article
{
    private ArticleId $id;
    private Title $title;
    private Description $description;
    private Content $content;
    private Url $url;
    private ImageUrl $imageUrl;
    private PublishedAt $publishedAt;
    private SourceName $sourceName;
    private Language $language;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        ArticleId $id,
        Title $title,
        Description $description,
        Content $content,
        Url $url,
        ImageUrl $imageUrl,
        PublishedAt $publishedAt,
        SourceName $sourceName,
        Language $language
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
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ArticleId
    {
        return $this->id;
    }

    public function getTitle(): Title
    {
        return $this->title;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getUrl(): Url
    {
        return $this->url;
    }

    public function getImageUrl(): ImageUrl
    {
        return $this->imageUrl;
    }

    public function getPublishedAt(): PublishedAt
    {
        return $this->publishedAt;
    }

    public function getSourceName(): SourceName
    {
        return $this->sourceName;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
