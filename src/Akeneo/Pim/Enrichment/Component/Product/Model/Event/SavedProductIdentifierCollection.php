<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model\Event;

class SavedProductIdentifierCollection
{
    private array $identifiers;

    private function __construct(array $identifiers)
    {
        $this->identifiers = $identifiers;
    }

    public static function fromProducts(array $products): SavedProductIdentifierCollection
    {
        return new SavedProductIdentifierCollection(array_map(fn ($product) =>  $product->getIdentifier(), $products));
    }

    public static function fromIdentifiers(array $productIdentifiers): SavedProductIdentifierCollection
    {
        return new SavedProductIdentifierCollection($productIdentifiers);
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }
}
