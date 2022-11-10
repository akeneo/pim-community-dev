<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;

use Ramsey\Uuid\Uuid;

final class ProductFileIdentifier
{
    private function __construct(private readonly string $identifier)
    {
        if (!Uuid::isValid($identifier)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The product file identifier must be a UUID, "%s" given.',
                    $identifier,
                ),
            );
        }
    }

    public static function fromString(string $identifier): self
    {
        return new self($identifier);
    }

    public function __toString(): string
    {
        return $this->identifier;
    }
}
