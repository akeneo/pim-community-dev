<?php

namespace spec\Akeneo\AssetManager\Infrastructure\Job;

use Akeneo\AssetManager\Application\Asset\ComputeTransformationsAssets\ComputeTransformationsExecutor;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Infrastructure\Job\ComputeTransformations;
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
        $jobParameters->get('asset_codes')->willReturn(['assetCode1', 'assetCode2']);

        $computeTransformationsExecutor
            ->execute([AssetCode::fromString('assetCode1'), AssetCode::fromString('assetCode2')])
            ->shouldBeCalledOnce();

        $this->execute();
    }
}
