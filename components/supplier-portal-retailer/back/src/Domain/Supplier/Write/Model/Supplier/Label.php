<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;

final class Label
{
    private const MAX_LENGTH = 200;

    private function __construct(private string $label)
    {
        if ('' === $label) {
            throw new \InvalidArgumentException('The supplier label cannot be empty.');
        }

        if (self::MAX_LENGTH < strlen($label)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The supplier label is too long. It should have %d characters or less.',
                    self::MAX_LENGTH,
                ),
            );
        }
    }

    public static function fromString(string $label): self
    {
        return new self($label);
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
