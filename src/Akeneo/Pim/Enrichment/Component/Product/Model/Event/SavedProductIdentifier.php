<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Event;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class SavedProductIdentifier
{
    private string $identifier;

    private function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function fromProduct(ProductInterface $product): SavedProductIdentifier
    {
        return new SavedProductIdentifier($product->getIdentifier());
    }

    public static function fromIdentifier(string $productIdentifier): SavedProductIdentifier
    {
        return new SavedProductIdentifier($productIdentifier);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
