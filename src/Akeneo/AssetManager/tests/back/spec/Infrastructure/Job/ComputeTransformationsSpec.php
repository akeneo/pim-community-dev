<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileTargetValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Job\ComputeTransformations;
use Akeneo\AssetManager\Infrastructure\Transformation\GetOutdatedVariationSource;
use Akeneo\AssetManager\Infrastructure\Transformation\TransformationExecutor;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ComputeTransformationsSpec extends ObjectBehavior
{
    function let(
        FindAssetIdentifiersByAssetFamilyInterface $findIdentifiersByAssetFamily,
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        TransformationExecutor $transformationExecutor,
        EditAssetHandler $editAssetHandler,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        JobExecution $jobExecution,
        ValidatorInterface $validator,
        CountAssetsInterface $countAssets,
        JobRepositoryInterface $jobRepository
    ) {
        $this->beConstructedWith(
            $findIdentifiersByAssetFamily,
            $getTransformations,
            $assetRepository,
            $getOutdatedVariationSource,
            $transformationExecutor,
            $editAssetHandler,
            $validator,
            $countAssets,
            $jobRepository,
            3
        );
        $executionContext = new ExecutionContext();
        $executionContext->put(JobInterface::WORKING_DIRECTORY_PARAMETER, '/jobexecution/working/directory');
        $jobExecution->getExecutionContext()->willReturn($executionContext);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_compute_transformations_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldBeAnInstanceOf(ComputeTransformations::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    function it_executes_the_compute_transformations_from_asset_identifiers(
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        TransformationExecutor $transformationExecutor,
        EditAssetHandler $editAssetHandler,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        Transformation $thumbnail,
        Asset $asset1,
        Asset $asset2,
        FileData $sourceFileData,
        EditMediaFileTargetValueCommand $command
    ) {
        $jobParameters->has('asset_family_identifier')->willReturn(false);
        $jobParameters->has('asset_identifiers')->willReturn(true);
        $jobParameters->get('asset_identifiers')->willReturn(['assetIdentifier1', 'assetIdentifier2']);

        $packshotIdentifier = AssetFamilyIdentifier::fromString('packshot');

        $asset1->getAssetFamilyIdentifier()->willReturn($packshotIdentifier);
        $asset1->getCode()->willReturn(AssetCode::fromString('asset_code_1'));
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier1'))->willReturn($asset1);
        $asset2->getAssetFamilyIdentifier()->willReturn($packshotIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier2'))->willReturn($asset2);

        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
        $thumbnail->getLabel()->willReturn(TransformationLabel::fromString('thumbnail'));
        $transformations = TransformationCollection::create([$thumbnail->getWrappedObject()]);

        $getTransformations->fromAssetFamilyIdentifier($packshotIdentifier)
                           ->shouldBeCalledOnce()->willReturn($transformations);

        $sourceFileData->normalize()->willReturn(['mimeType' => 'image/png']);
        $getOutdatedVariationSource->forAssetAndTransformation($asset1, $thumbnail)->willReturn($sourceFileData);
        $getOutdatedVariationSource->forAssetAndTransformation($asset2, $thumbnail)->willReturn(null);
        $stepExecution->incrementSummaryInfo('skipped')->shouldBeCalled();
        $transformationExecutor->execute(
            $sourceFileData,
            $packshotIdentifier,
            $thumbnail,
            '/jobexecution/working/directory'
        )->willReturn($command);

        $validator->validate($command)->willReturn([]);

        $editAssetHandler->__invoke(
            new EditAssetCommand(
                'packshot',
                'asset_code_1',
                [$command->getWrappedObject()]
            )
        )->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('transformations', 1)->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(2);

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
        FindAssetIdentifiersByAssetFamilyInterface $findIdentifiersByAssetFamily,
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        TransformationExecutor $transformationExecutor,
        EditAssetHandler $editAssetHandler,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        Transformation $thumbnail,
        Asset $asset1,
        Asset $asset2,
        FileData $sourceFileData,
        EditMediaFileTargetValueCommand $command,
        \Iterator $assetIdentifiers,
        CountAssetsInterface $countAssets
    ) {
        $jobParameters->has('asset_family_identifier')->willReturn(true);
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $assetIdentifier1 = AssetIdentifier::fromString('assetIdentifier1');
        $assetIdentifier2 = AssetIdentifier::fromString('assetIdentifier2');

        $assetIdentifiers->valid()->willReturn(true, true, false);
        $assetIdentifiers->current()->willReturn($assetIdentifier1, $assetIdentifier2);
        $assetIdentifiers->rewind()->shouldBeCalled();
        $assetIdentifiers->next()->shouldBeCalled();

        $findIdentifiersByAssetFamily
            ->find(AssetFamilyIdentifier::fromString('packshot'))
            ->willReturn($assetIdentifiers);

        $countAssets->forAssetFamily($assetFamilyIdentifier)->shouldBeCalledOnce()->willReturn(2);

        $stepExecution->setTotalItems(2)->shouldBeCalledOnce();
        $thumbnail->getLabel()->willReturn(TransformationLabel::fromString('thumbnail'));
        $transformations = TransformationCollection::create([$thumbnail->getWrappedObject()]);
        $getTransformations->fromAssetFamilyIdentifier($assetFamilyIdentifier)
            ->shouldBeCalledOnce()->willReturn($transformations);

        $asset1->getCode()->willReturn(AssetCode::fromString('asset_code_1'));
        $asset1->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $assetRepository->getByIdentifier($assetIdentifier1)->willReturn($asset1);

        $asset2->getCode()->willReturn(AssetCode::fromString('asset_code_2'));
        $asset2->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $assetRepository->getByIdentifier($assetIdentifier2)->willReturn($asset2);

        $sourceFileData->normalize()->willReturn(['mimeType' => 'image/png']);
        $getOutdatedVariationSource->forAssetAndTransformation($asset1, $thumbnail)->willReturn($sourceFileData);
        $getOutdatedVariationSource->forAssetAndTransformation($asset2, $thumbnail)->willReturn(null);
        $stepExecution->incrementSummaryInfo('skipped')->shouldBeCalled();
        $transformationExecutor->execute(
            $sourceFileData,
            $assetFamilyIdentifier,
            $thumbnail,
            '/jobexecution/working/directory'
        )->willReturn($command);

        $validator->validate($command)->willReturn([]);

        $editAssetHandler->__invoke(
            new EditAssetCommand(
                'packshot',
                'asset_code_1',
                [$command->getWrappedObject()]
            )
        )->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('transformations', 1)->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(2);

        $editAssetHandler->__invoke(
            new EditAssetCommand(
                'packshot',
                'asset_code_2',
                [Argument::any()]
            )
        )->shouldNotBeCalled();

        $this->execute();
    }

    function it_batch_asset_compute_transformations(
        GetTransformations $getTransformations,
        AssetRepositoryInterface $assetRepository,
        GetOutdatedVariationSource $getOutdatedVariationSource,
        TransformationExecutor $transformationExecutor,
        EditAssetHandler $editAssetHandler,
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        Transformation $thumbnail,
        Asset $asset1,
        Asset $asset2,
        Asset $asset3,
        Asset $asset4,
        FileData $sourceFileData,
        EditMediaFileTargetValueCommand $command,
        JobRepositoryInterface $jobRepository
    ) {
        $jobParameters->has('asset_family_identifier')->willReturn(false);
        $jobParameters->has('asset_identifiers')->willReturn(true);
        $jobParameters->get('asset_identifiers')->willReturn([
            'assetIdentifier1',
            'assetIdentifier2',
            'assetIdentifier3',
            'assetIdentifier4'
        ]);

        $packshotIdentifier = AssetFamilyIdentifier::fromString('packshot');

        $asset1->getAssetFamilyIdentifier()->willReturn($packshotIdentifier);
        $asset1->getCode()->willReturn(AssetCode::fromString('asset_code_1'));
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier1'))->willReturn($asset1);
        $asset2->getAssetFamilyIdentifier()->willReturn($packshotIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier2'))->willReturn($asset2);
        $asset3->getAssetFamilyIdentifier()->willReturn($packshotIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier3'))->willReturn($asset3);
        $asset4->getAssetFamilyIdentifier()->willReturn($packshotIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier4'))->willReturn($asset4);

        $stepExecution->setTotalItems(4)->shouldBeCalledOnce();
        $thumbnail->getLabel()->willReturn(TransformationLabel::fromString('thumbnail'));
        $transformations = TransformationCollection::create([$thumbnail->getWrappedObject()]);

        $getTransformations->fromAssetFamilyIdentifier($packshotIdentifier)->willReturn($transformations);

        $sourceFileData->normalize()->willReturn(['mimeType' => 'image/png']);
        $getOutdatedVariationSource->forAssetAndTransformation($asset1, $thumbnail)->willReturn($sourceFileData);
        $getOutdatedVariationSource->forAssetAndTransformation($asset2, $thumbnail)->willReturn(null);
        $getOutdatedVariationSource->forAssetAndTransformation($asset3, $thumbnail)->willReturn(null);
        $getOutdatedVariationSource->forAssetAndTransformation($asset4, $thumbnail)->willReturn(null);
        $stepExecution->incrementSummaryInfo('skipped')->shouldBeCalled();
        $transformationExecutor->execute(
            $sourceFileData,
            $packshotIdentifier,
            $thumbnail,
            '/jobexecution/working/directory'
        )->willReturn($command);

        $validator->validate($command)->willReturn([]);

        $editAssetHandler->__invoke(
            new EditAssetCommand(
                'packshot',
                'asset_code_1',
                [$command->getWrappedObject()]
            )
        )->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('transformations', 1)->shouldBeCalled();
        $stepExecution->incrementProcessedItems()->shouldBeCalledTimes(4);
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(2);

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
