<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;

final class Filename
{
    private function __construct(private string $originalFilename)
    {
        if ('' === trim($originalFilename)) {
            throw new \InvalidArgumentException('The filename cannot be empty.');
        }
    }

    public static function fromString(string $originalFilename): self
    {
        return new self($originalFilename);
    }

    public function __toString(): string
    {
        return $this->originalFilename;
    }
}
