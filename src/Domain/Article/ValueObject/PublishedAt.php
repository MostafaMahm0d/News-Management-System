<?php

declare(strict_types=1);

namespace App\Domain\Article\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

class PublishedAt
{
    private DateTimeImmutable $value;

    public function __construct(DateTimeImmutable $value)
    {
        if ($value > new DateTimeImmutable()) {
            throw new InvalidArgumentException('Published date cannot be in the future');
        }

        $this->value = $value;
    }

    public static function fromString(string $date): self
    {
        try {
            $dateTime = new DateTimeImmutable($date);
            return new self($dateTime);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid date format: ' . $date);
        }
    }

    public function getValue(): DateTimeImmutable
    {
        return $this->value;
    }

    public function format(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->value->format($format);
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
