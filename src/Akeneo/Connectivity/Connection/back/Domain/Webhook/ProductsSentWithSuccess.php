<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook;

final class ProductsSentWithSuccess
{
    private array $productIdentifiers;

    private function __construct(array $productIdentifiers)
    {
        $this->productIdentifiers = $productIdentifiers;
    }

    public static function withIdentifiers(array $productIdentifiers): self
    {
        return new self($productIdentifiers);
    }

    public function getIdentifiers(): array
    {
        return $this->productIdentifiers;
    }
}
