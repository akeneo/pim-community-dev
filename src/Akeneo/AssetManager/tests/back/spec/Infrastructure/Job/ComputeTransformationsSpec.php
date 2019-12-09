<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindSearchableAssetsInterface;
use Akeneo\AssetManager\Domain\Query\Asset\SearchableAssetItem;
use Akeneo\AssetManager\Infrastructure\Job\ComputeTransformations;
use Akeneo\AssetManager\Infrastructure\Transformation\ComputeTransformationsExecutor;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class ComputeTransformationsSpec extends ObjectBehavior
{
    function let(
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        FindSearchableAssetsInterface $findSearchableAssets,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($computeTransformationsExecutor, $findSearchableAssets);
        $this->setStepExecution($stepExecution);
        $this->shouldHaveType(ComputeTransformations::class);
    }

    function it_executes_the_compute_transformations_from_asset_identifiers(
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('asset_family_identifier')->willReturn(false);
        $jobParameters->has('asset_identifiers')->willReturn(true);
        $jobParameters->get('asset_identifiers')->willReturn(['assetIdentifier1', 'assetIdentifier2']);

        $computeTransformationsExecutor
            ->execute([AssetIdentifier::fromString('assetIdentifier1'), AssetIdentifier::fromString('assetIdentifier2')])
            ->shouldBeCalledOnce();

        $this->execute();
    }

    function it_executes_the_compute_transformations_from_asset_family_identifier(
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        FindSearchableAssetsInterface $findSearchableAssets,
        StepExecution $stepExecution,
        JobParameters $jobParameters,
        \Iterator $iterator,
        SearchableAssetItem $asset1,
        SearchableAssetItem $asset2
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('asset_family_identifier')->willReturn(true);
        $jobParameters->get('asset_family_identifier')->willReturn('packshot');

        $findSearchableAssets->byAssetFamilyIdentifier(AssetFamilyIdentifier::fromString('packshot'))->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $asset1 = new SearchableAssetItem();
        $asset1->identifier = 'assetIdentifier1';
        $asset2 = new SearchableAssetItem();
        $asset2->identifier = 'assetIdentifier2';
        $iterator->current()->willReturn($asset1, $asset2);
        $iterator->next()->shouldBeCalled();

        $computeTransformationsExecutor
            ->execute([AssetIdentifier::fromString('assetIdentifier1'), AssetIdentifier::fromString('assetIdentifier2')])
            ->shouldBeCalledOnce();

        $this->execute();
    }
}
