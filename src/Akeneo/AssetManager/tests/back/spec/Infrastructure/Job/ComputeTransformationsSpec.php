<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\Asset\FindSearchableAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\SearchableAssetItem;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Job\ComputeTransformations;
use Akeneo\AssetManager\Infrastructure\Transformation\ComputeTransformationsExecutor;
use Akeneo\AssetManager\Infrastructure\Transformation\GetOutdatedVariationSource;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ComputeTransformationsSpec extends ObjectBehavior
{
    function let(
        FindSearchableAssetsInterface $findSearchableAssets,
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        EditAssetHandler $editAssetHandler,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(
            $findSearchableAssets,
            $getTransformations,
            $assetRepository,
            $getOutdatedVariationSource,
            $computeTransformationsExecutor,
            $editAssetHandler
        );
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_compute_transformations_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldBeAnInstanceOf(ComputeTransformations::class);
    }

    function it_executes_the_compute_transformations_from_asset_identifiers(
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        EditAssetHandler $editAssetHandler,
        JobParameters $jobParameters,
        Transformation $thumbnail,
        Asset $asset1,
        Asset $asset2,
        FileData $sourceFileData,
        EditMediaFileValueCommand $command
    ) {
        $jobParameters->has('asset_family_identifier')->willReturn(false);
        $jobParameters->has('asset_identifiers')->willReturn(true);
        $jobParameters->get('asset_identifiers')->willReturn(['assetIdentifier1', 'assetIdentifier2']);

        $thumbnail->getLabel()->willReturn(TransformationLabel::fromString('thumbnail'));
        $transformations = TransformationCollection::create([$thumbnail->getWrappedObject()]);
        $getTransformations->fromAssetIdentifiers(
            [
                AssetIdentifier::fromString('assetIdentifier1'),
                AssetIdentifier::fromString('assetIdentifier2'),
            ]
        )->willReturn(
            [
                'assetIdentifier1' => $transformations,
                'assetIdentifier2' => $transformations,
            ]
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');

        $asset1->getCode()->willReturn(AssetCode::fromString('asset_code_1'));
        $asset1->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier1'))->willReturn($asset1);

        $asset2->getCode()->willReturn(AssetCode::fromString('asset_code_2'));
        $asset2->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier2'))->willReturn($asset2);

        $getOutdatedVariationSource->forAssetAndTransformation($asset1, $thumbnail)->willReturn($sourceFileData);
        $getOutdatedVariationSource->forAssetAndTransformation($asset2, $thumbnail)->willReturn(null);
        $computeTransformationsExecutor->execute($sourceFileData, $assetFamilyIdentifier, $thumbnail)
                                       ->willReturn($command);

        $editAssetHandler->__invoke(
            new EditAssetCommand(
                'packshot',
                'asset_code_1',
                [$command->getWrappedObject()]
            )
        )->shouldBeCalled();
        $editAssetHandler->__invoke(
            new EditAssetCommand(
                'packshot',
                'asset_code_2',
                [Argument::any()]
            )
        )->shouldNotBeCalled();

        $this->execute();
    }

    function it_executes_the_compute_transformations_from_asset_family_identifier(
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        FindSearchableAssetsInterface $findSearchableAssets,
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        EditAssetHandler $editAssetHandler,
        JobParameters $jobParameters,
        Transformation $thumbnail,
        Asset $asset1,
        Asset $asset2,
        FileData $sourceFileData,
        EditMediaFileValueCommand $command
    ) {
        $jobParameters->has('asset_family_identifier')->willReturn(true);
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');

        $searchableAssetItem1 = new SearchableAssetItem();
        $searchableAssetItem1->identifier = 'assetIdentifier1';
        $searchableAssetItem2 = new SearchableAssetItem();
        $searchableAssetItem2->identifier = 'assetIdentifier2';

        $findSearchableAssets->byAssetFamilyIdentifier(AssetFamilyIdentifier::fromString('packshot'))->willReturn(
            new \ArrayIterator([$searchableAssetItem1, $searchableAssetItem2])
        );

        $thumbnail->getLabel()->willReturn(TransformationLabel::fromString('thumbnail'));
        $transformations = TransformationCollection::create([$thumbnail->getWrappedObject()]);
        $getTransformations->fromAssetIdentifiers(
            [
                AssetIdentifier::fromString('assetIdentifier1'),
                AssetIdentifier::fromString('assetIdentifier2'),
            ]
        )->willReturn(
            [
                'assetIdentifier1' => $transformations,
                'assetIdentifier2' => $transformations,
            ]
        );

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');

        $asset1->getCode()->willReturn(AssetCode::fromString('asset_code_1'));
        $asset1->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier1'))->willReturn($asset1);

        $asset2->getCode()->willReturn(AssetCode::fromString('asset_code_2'));
        $asset2->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier2'))->willReturn($asset2);

        $getOutdatedVariationSource->forAssetAndTransformation($asset1, $thumbnail)->willReturn($sourceFileData);
        $getOutdatedVariationSource->forAssetAndTransformation($asset2, $thumbnail)->willReturn(null);
        $computeTransformationsExecutor->execute($sourceFileData, $assetFamilyIdentifier, $thumbnail)
                                       ->willReturn($command);

        $editAssetHandler->__invoke(
            new EditAssetCommand(
                'packshot',
                'asset_code_1',
                [$command->getWrappedObject()]
            )
        )->shouldBeCalled();
        $editAssetHandler->__invoke(
            new EditAssetCommand(
                'packshot',
                'asset_code_2',
                [Argument::any()]
            )
        )->shouldNotBeCalled();

        $this->execute();
    }
}
