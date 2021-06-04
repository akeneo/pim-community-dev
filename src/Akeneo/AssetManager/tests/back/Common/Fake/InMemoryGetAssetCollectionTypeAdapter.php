<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\GetAssetCollectionTypeAdapterInterface;

class InMemoryGetAssetCollectionTypeAdapter implements GetAssetCollectionTypeAdapterInterface
{
    private ?string $attributeType = null;

    private ?\Exception $exceptionToThrow = null;

    public function fetch(string $productAttributeCode): string
    {
        if ($this->exceptionToThrow) {
            throw new $this->exceptionToThrow;
        }

        return $this->attributeType;
    }

    public function stubWith(string $attributeType): void
    {
        $this->attributeType = $attributeType;
    }

    public function stubWithException(\Exception $exceptionToThrow): void
    {
        $this->exceptionToThrow = $exceptionToThrow;
    }
}
