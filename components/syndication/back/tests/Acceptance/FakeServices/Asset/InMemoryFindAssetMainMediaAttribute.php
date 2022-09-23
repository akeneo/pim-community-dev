<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\Asset;

use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\AttributeAsMainMedia;
use Akeneo\Platform\Syndication\Domain\Query\AssetCollection\FindAssetMainMediaAttributeInterface;

final class InMemoryFindAssetMainMediaAttribute implements FindAssetMainMediaAttributeInterface
{
    private array $attributesAsMainMedia = [];

    public function addAttributeAsMainMedia(string $assetFamilyCode, AttributeAsMainMedia $data): void
    {
        $this->attributesAsMainMedia[$assetFamilyCode] = $data;
    }

    public function forAssetFamily(string $assetFamilyCode): AttributeAsMainMedia
    {
        return $this->attributesAsMainMedia[$assetFamilyCode];
    }
}
