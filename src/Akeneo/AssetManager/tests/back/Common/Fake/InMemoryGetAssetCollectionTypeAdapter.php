<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\GetAssetCollectionTypeAdapterInterface;

class InMemoryGetAssetCollectionTypeAdapter implements GetAssetCollectionTypeAdapterInterface
{
    private $attributeType;

    public function fetch(string $productAttributeCode): string
    {
        return $this->attributeType;
    }

    public function stubWith(string $attributeType): void
    {
        $this->attributeType = $attributeType;
    }
}
