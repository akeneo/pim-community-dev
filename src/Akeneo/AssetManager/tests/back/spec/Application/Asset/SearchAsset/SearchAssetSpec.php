<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\SearchAsset;

use Akeneo\AssetManager\Application\Asset\SearchAsset\SearchAsset;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersForQueryInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetItemsForIdentifiersAndQueryInterface;
use Akeneo\AssetManager\Domain\Query\Asset\IdentifiersForQueryResult;
use Akeneo\AssetManager\Domain\Query\Asset\AssetItem;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SearchAssetSpec extends ObjectBehavior
{
    function let(
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        FindAssetItemsForIdentifiersAndQueryInterface $findAssetItemsForIdentifiersAndQuery,
        CountAssetsInterface $countAssets
    ) {
        $this->beConstructedWith($findIdentifiersForQuery, $findAssetItemsForIdentifiersAndQuery, $countAssets);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SearchAsset::class);
    }

    function it_returns_search_result_from_a_asset_query(
        FindIdentifiersForQueryInterface $findIdentifiersForQuery,
        FindAssetItemsForIdentifiersAndQueryInterface $findAssetItemsForIdentifiersAndQuery,
        CountAssetsInterface $countAssets,
        AssetItem $stark,
        AssetItem $dyson
    ) {
        $stark->normalize()->willReturn(['identifier' => 'stark']);
        $dyson->normalize()->willReturn(['identifier' => 'dyson']);
        $assetQuery = AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            1,
            null,
            []
        );
        $identifiersResult = new IdentifiersForQueryResult(['stark', 'dyson'], 2);
        $findIdentifiersForQuery->find($assetQuery)->willReturn($identifiersResult);
        $findAssetItemsForIdentifiersAndQuery->find(['stark', 'dyson'], $assetQuery)
            ->willReturn([$stark, $dyson]);
        $countAssets->forAssetFamily(
            Argument::that(
                fn(AssetFamilyIdentifier $assetFamilyIdentifier) => 'brand' === (string) $assetFamilyIdentifier
            )
        )->willReturn(10);

        $result = $this->__invoke($assetQuery);

        $result->normalize()->shouldReturn([
            'items' => [
                ['identifier' => 'stark'],
                ['identifier' => 'dyson']
            ],
            'matches_count' => 2,
            'total_count' => 10,
        ]);
    }
}
