<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;

use Ramsey\Uuid\Uuid;

final class Identifier
{
    private function __construct(private string $identifier)
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
