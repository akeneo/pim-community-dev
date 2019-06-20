<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\AssetItem;
use Akeneo\AssetManager\Domain\Query\Asset\SearchAssetResult;
use PhpSpec\ObjectBehavior;

class SearchAssetResultSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith([], 5, 10);
        $this->shouldHaveType(SearchAssetResult::class);
    }

    function it_normalizes_itself(AssetItem $assetItem)
    {
        $this->beConstructedWith([$assetItem], 1, 2);
        $assetItem->normalize()->willReturn(['identifier' => 'asset_identifier']);
        $this->normalize()->shouldReturn([
            'items'         => [
                [
                    'identifier' => 'asset_identifier',
                ],
            ],
            'matches_count' => 1,
            'total_count'   => 2,
        ]);
    }

    function it_can_be_constructed_only_with_a_list_of_asset_items()
    {
        $this->beConstructedWith([1], 5, 10);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
