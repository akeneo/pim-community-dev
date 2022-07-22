<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject;

final class Path
{
    private function __construct(private string $path)
    {
        if ('' === trim($path)) {
            throw new \InvalidArgumentException('The path cannot be empty.');
        }
    }

    public static function fromString(string $path): self
    {
        return new self($path);
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
