<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetIndexer;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\AssetNormalizerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Box\Spout\Reader\IteratorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetIndexerSpec extends ObjectBehavior
{
    function let(Client $assetEsCLient, AssetNormalizerInterface $assetNormalizer)
    {
        $this->beConstructedWith($assetEsCLient, $assetNormalizer, 2);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetIndexer::class);
    }

    function it_indexes_one_asset(Client $assetEsCLient, AssetNormalizerInterface $assetNormalizer)
    {
        $assetIdentifier = AssetIdentifier::create('designer', 'coco', 'finger');
        $assetNormalizer->normalizeAsset($assetIdentifier)->willReturn(['identifier' => 'stark']);
        $assetEsCLient->index('stark', ['identifier' => 'stark'],
            Argument::type(Refresh::class))
            ->shouldBeCalled();

        $this->index($assetIdentifier);
    }

    function it_index_assets_by_asset_family_identifier_and_by_batch(
        Client $assetEsCLient,
        AssetNormalizerInterface $assetNormalizer,
        IteratorInterface $assetIterator
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetNormalizer->normalizeAssetsByAssetFamily($assetFamilyIdentifier)->willReturn($assetIterator);
        $assetIterator->valid()->willReturn(true, true, true, false);
        $assetIterator->current()->willReturn(['identifier' => 'stark'], ['identifier' => 'coco'], ['identifier' => 'another_asset']);
        $assetIterator->next()->shouldBeCalled();
        $assetIterator->rewind()->shouldBeCalled();

        $assetEsCLient->bulkIndexes([['identifier' => 'stark'], ['identifier' => 'coco']],'identifier', Argument::type(Refresh::class))
            ->shouldBeCalled();
        $assetEsCLient->bulkIndexes([['identifier' => 'another_asset']],'identifier', Argument::type(Refresh::class))
            ->shouldBeCalled();

        $this->indexByAssetFamily($assetFamilyIdentifier);
    }

    function it_removes_one_asset(Client $assetEsCLient)
    {
        $assetEsCLient->deleteByQuery(
            [
                "query" => [
                    "bool" => [
                        "must" => [
                            ["term" => ["asset_family_code" => "designer"]],
                            ["term" => ["code" => "stark"]],
                        ],
                    ],
                ],
            ])->shouldBeCalled();

        $this->removeAssetByAssetFamilyIdentifierAndCode('designer', 'stark');
    }

    function it_removes_all_refenrence_entity_assets(Client $assetEsCLient)
    {
        $assetEsCLient->deleteByQuery(
            [
                'query' => [
                    'match' => ['asset_family_code' => 'designer'],
                ],
            ])->shouldBeCalled();

        $this->removeByAssetFamilyIdentifier('designer');
    }
}
