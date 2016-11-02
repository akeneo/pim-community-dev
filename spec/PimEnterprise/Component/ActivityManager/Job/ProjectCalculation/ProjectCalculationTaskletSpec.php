<?php

namespace spec\Akeneo\ActivityManager\Component\Job\ProjectCalculation;

use Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\ActivityManager\Component\Job\ProjectCalculation\ProjectCalculationTasklet;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\ActivityManager\Component\Repository\ProductRepositoryInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\Step\TaskletInterface;

class ProjectCalculationTaskletSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        ProjectRepositoryInterface $projectRepository,
        CalculationStepInterface $chainCalculationStep,
        SaverInterface $projectSaver,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->beConstructedWith(
            $productRepository,
            $projectRepository,
            $chainCalculationStep,
            $projectSaver,
            $objectDetacher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCalculationTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_has_a_step_execution(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn(null);
    }

    function it_calculates_a_project(
        $productRepository,
        $projectRepository,
        $chainCalculationStep,
        $projectSaver,
        $objectDetacher,
        StepExecution $stepExecution,
        ProjectInterface $project,
        ProductInterface $product,
        ProductInterface $otherProduct,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willreturn($jobParameters);
        $jobParameters->get('project_id')->willReturn(42);

        $projectRepository->find(42)->willReturn($project);

        $productRepository->findByProject($project)->willReturn([$product, $otherProduct]);

        $chainCalculationStep->execute($product, $project);
        $chainCalculationStep->execute($otherProduct, $project);

        $objectDetacher->detach($product)->shouldBeCalled();
        $objectDetacher->detach($otherProduct)->shouldBeCalled();

        $projectSaver->save($project);

        $this->execute()->shouldReturn(null);
    }
}
