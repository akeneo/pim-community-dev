<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\RefreshAssets;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;

class RefreshAssetSpec extends ObjectBehavior
{
    function let(AssetRepositoryInterface $assetRepository)
    {
        $this->beConstructedWith($assetRepository);
    }

    function it_refreshes_a_asset_by_loading_it_and_updating_it(
        AssetRepositoryInterface $assetRepository,
        Asset $assetToRefresh
    ) {
        $assetIdentifier = AssetIdentifier::fromString('a_asset_to_refresh');
        $assetRepository->getByIdentifier($assetIdentifier)->willReturn($assetToRefresh);
        $assetRepository->update($assetToRefresh)->shouldBeCalled();

        $this->refresh($assetIdentifier);
    }
}
