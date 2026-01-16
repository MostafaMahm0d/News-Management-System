<?php

declare(strict_types=1);

namespace App\Domain\Article\ValueObject;

class SourceName
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Source name cannot be empty');
        }

        if (strlen($value) > 255) {
            throw new \InvalidArgumentException('Source name cannot exceed 255 characters');
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
