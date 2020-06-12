<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\ValueObject;

use Webmozart\Assert\Assert;

class Title
{
    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        Assert::stringNotEmpty($value, 'Title cannot be empty');

        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Title $value): bool
    {
        return $this->value === (string) $value;
    }

    public function normalize(): string
    {
        return $this->value;
    }
}
