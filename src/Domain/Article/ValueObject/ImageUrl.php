<?php

declare(strict_types=1);

namespace App\Domain\Article\ValueObject;

class ImageUrl
{
    private ?string $value;

    public function __construct(?string $value)
    {
        if ($value !== null && !empty(trim($value))) {
            if (!filter_var(
                filter_var($value, FILTER_SANITIZE_URL),
                FILTER_VALIDATE_URL
            )) {
                throw new \InvalidArgumentException('Invalid image URL format');
            }
        }

        $this->value = $value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value ?? '';
    }
}
