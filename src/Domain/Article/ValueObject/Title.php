<?php

declare(strict_types=1);

namespace App\Domain\Article\ValueObject;

class Title
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (strlen($value) > 500) {
            throw new \InvalidArgumentException('Title cannot exceed 500 characters');
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
