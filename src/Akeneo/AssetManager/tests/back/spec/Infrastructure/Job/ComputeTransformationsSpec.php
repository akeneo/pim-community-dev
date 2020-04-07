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
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetIdentifiersByAssetFamilyInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Job\ComputeTransformations;
use Akeneo\AssetManager\Infrastructure\Transformation\GetOutdatedVariationSource;
use Akeneo\AssetManager\Infrastructure\Transformation\TransformationExecutor;
use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
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
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $findIdentifiersByAssetFamily,
            $getTransformations,
            $assetRepository,
            $getOutdatedVariationSource,
            $transformationExecutor,
            $editAssetHandler,
            $validator
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
        EditMediaFileTargetValueCommand $command
    ) {
        $jobParameters->has('asset_family_identifier')->willReturn(true);
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');

        $findIdentifiersByAssetFamily->find(AssetFamilyIdentifier::fromString('packshot'))->willReturn(
            new \ArrayIterator(
                [
                    AssetIdentifier::fromString('assetIdentifier1'),
                    AssetIdentifier::fromString('assetIdentifier2'),
                ]
            )
        );

        $thumbnail->getLabel()->willReturn(TransformationLabel::fromString('thumbnail'));
        $transformations = TransformationCollection::create([$thumbnail->getWrappedObject()]);
        $getTransformations->fromAssetFamilyIdentifier($assetFamilyIdentifier)
            ->shouldBeCalledOnce()->willReturn($transformations);

        $asset1->getCode()->willReturn(AssetCode::fromString('asset_code_1'));
        $asset1->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier1'))->willReturn($asset1);

        $asset2->getCode()->willReturn(AssetCode::fromString('asset_code_2'));
        $asset2->getAssetFamilyIdentifier()->willReturn($assetFamilyIdentifier);
        $assetRepository->getByIdentifier(AssetIdentifier::fromString('assetIdentifier2'))->willReturn($asset2);

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
