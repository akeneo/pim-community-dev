<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Test\Acceptance\FakeServices\Asset;

use Akeneo\Platform\Syndication\Domain\Query\FindAssetMainMediaDataInterface;

final class InMemoryFindAssetMainMediaData implements FindAssetMainMediaDataInterface
{
    private array $assetMainMediaData = [];

    /**
     * @param mixed $data
     */
    public function addAssetMainMediaData(
        string $assetFamilyIdentifier,
        string $assetCode,
        ?string $channel,
        ?string $locale,
        $data
    ): void {
        $this->assetMainMediaData[$assetFamilyIdentifier][$assetCode][$channel ?? 'null'][$locale ?? 'null'] = $data;
    }

    public function forAssetFamilyAndAssetCodes(string $assetFamilyIdentifier, array $assetCodes, ?string $channel, ?string $locale): array
    {
        return array_filter(array_reduce($assetCodes, function ($carry, $assetCode) use ($assetFamilyIdentifier, $channel, $locale) {
            $carry[$assetCode] = $this->assetMainMediaData[$assetFamilyIdentifier][$assetCode][$channel ?? 'null'][$locale ?? 'null'] ?? null;

            return $carry;
        }, []), static fn ($data) => null == !$data);
    }
}
