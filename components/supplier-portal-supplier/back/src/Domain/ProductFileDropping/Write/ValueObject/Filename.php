<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject;

final class Filename
{
    private function __construct(private string $filename)
    {
        if ('' === trim($filename)) {
            throw new \InvalidArgumentException('The filename cannot be empty.');
        }
    }

    public static function fromString(string $filename): self
    {
        return new self($filename);
    }

    public function __toString(): string
    {
        return $this->filename;
    }
}
