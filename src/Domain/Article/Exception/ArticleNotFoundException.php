<?php

declare(strict_types=1);

namespace App\Domain\Article\Exception;

use Exception;

class ArticleNotFoundException extends Exception
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Article with ID "%s" not found', $id));
    }
}
