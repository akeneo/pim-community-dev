<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Reader\Database;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Search\Elasticsearch\Asset\CountAssets;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Reader\Database\AssetReader;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class AssetReaderSpec extends ObjectBehavior
{
    function let(
        FindAssetIdentifiersByAssetFamilyInterface $findAssetIdentifiersByAssetFamily,
        AssetRepositoryInterface $assetRepository,
        StepExecution $stepExecution,
        JobParameters $parameters,
        CountAssets $countAssets
    ) {
        $this->beConstructedWith($findAssetIdentifiersByAssetFamily, $assetRepository, $countAssets);

        $parameters->get('asset_family_identifier')->willReturn('packshot');
        $stepExecution->getJobParameters()->willReturn($parameters);
        $this->setStepExecution($stepExecution);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $findAssetIdentifiersByAssetFamily->find($assetFamilyIdentifier)->willReturn(
            new \ArrayIterator(
                [
                    AssetIdentifier::fromString('asset_packshot_1'),
                    AssetIdentifier::fromString('asset_packshot_2'),
                    AssetIdentifier::fromString('asset_packshot_3'),
                ]
            )
        );
        $this->initialize();
    }

    function it_is_an_item_reader()
    {
        $this->shouldImplement(ItemReaderInterface::class);
    }

    function it_is_a_database_asset_reader()
    {
        $this->shouldHaveType(AssetReader::class);
    }

    function it_reads_items_from_database(
        AssetRepositoryInterface $assetRepository,
        StepExecution $stepExecution
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        [$asset1, $asset2, $asset3] = [
            $this->createAsset(AssetIdentifier::fromString('asset_packshot_1'), $assetFamilyIdentifier),
            $this->createAsset(AssetIdentifier::fromString('asset_packshot_2'), $assetFamilyIdentifier),
            $this->createAsset(AssetIdentifier::fromString('asset_packshot_3'), $assetFamilyIdentifier),
        ];
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('asset_packshot_1'))->willReturn($asset1);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('asset_packshot_2'))->willReturn($asset2);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('asset_packshot_3'))->willReturn($asset3);

        $stepExecution->incrementSummaryInfo('read')->shouldBeCalledTimes(3);

        $this->read()->shouldReturn($asset1);
        $this->read()->shouldReturn($asset2);
        $this->read()->shouldReturn($asset3);
        $this->read()->shouldReturn(null);
    }

    function it_counts_the_total_item_to_process(CountAssets $countAssets)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $countAssets->forAssetFamily($assetFamilyIdentifier)->willReturn(15);

        $this->totalItems()->shouldReturn(15);
    }

    private function createAsset(AssetIdentifier $identifier, AssetFamilyIdentifier $familyIdentifier): Asset
    {
        return Asset::create(
            $identifier,
            $familyIdentifier,
            AssetCode::fromString($identifier->__toString()),
            ValueCollection::fromValues([])
        );
    }
}
