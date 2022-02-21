<?php

declare(strict_types=1);

namespace Akeneo\Onboarder\Domain\Supplier;

final class Label
{
    private const MAX_LENGTH = 100;

    private string $label;

    private function __construct(string $label)
    {
        if ('' === $label) {
            throw new \InvalidArgumentException('The supplier label cannot be empty.');
        }

        if (strlen($label) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The supplier label is too long. It should have %d characters or less.',
                    self::MAX_LENGTH
                )
            );
        }

        $this->label = $label;
    }

    public static function fromString(string $label): self
    {
        return new self($label);
    }

    public function __toString(): string
    {
        return $this->label;
    }

    public function equals(self $other): bool
    {
        return $this->label === $other->label;
    }
}
