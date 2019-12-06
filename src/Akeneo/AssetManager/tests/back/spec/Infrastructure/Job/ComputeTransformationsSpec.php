<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Infrastructure\Job\ComputeTransformations;
use Akeneo\AssetManager\Infrastructure\Transformation\ComputeTransformationsExecutor;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class ComputeTransformationsSpec extends ObjectBehavior
{
    function let(
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith($computeTransformationsExecutor);
        $this->setStepExecution($stepExecution);
        $this->shouldHaveType(ComputeTransformations::class);
    }

    function it_executes_the_compute_transformations(
        ComputeTransformationsExecutor $computeTransformationsExecutor,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('asset_identifiers')->willReturn(['assetIdentifier1', 'assetIdentifier2']);

        $computeTransformationsExecutor
            ->execute([AssetIdentifier::fromString('assetIdentifier1'), AssetIdentifier::fromString('assetIdentifier2')])
            ->shouldBeCalledOnce();

        $this->execute();
    }
}
