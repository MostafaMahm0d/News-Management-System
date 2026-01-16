<?php

declare(strict_types=1);

namespace App\Domain\Article\ValueObject;

class Language
{
    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));

        if (empty($value)) {
            throw new \InvalidArgumentException('Language cannot be empty');
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

    public static function getValidLanguages(): array
    {
        return self::VALID_LANGUAGES;
    }
}
