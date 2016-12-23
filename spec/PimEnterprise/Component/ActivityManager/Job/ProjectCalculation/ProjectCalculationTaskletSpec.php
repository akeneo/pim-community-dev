<?php

namespace spec\PimEnterprise\Component\ActivityManager\Job\ProjectCalculation;

use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\ProjectCalculationTasklet;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProductRepositoryInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\Step\TaskletInterface;

class ProjectCalculationTaskletSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
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
        $jobParameters->get('project_code')->willReturn('project_code');

        $projectRepository->findOneByIdentifier('project_code')->willReturn($project);

        $productRepository->findByProject($project)->willReturn([$product, $otherProduct]);

        $chainCalculationStep->execute($product, $project);
        $chainCalculationStep->execute($otherProduct, $project);

        $objectDetacher->detach($product)->shouldBeCalled();
        $objectDetacher->detach($otherProduct)->shouldBeCalled();

        $projectSaver->save($project);

        $this->execute()->shouldReturn(null);
    }

    function it_throw_a_logic_exception_if_we_run_a_calculation_on_non_existing_project(
        $projectRepository,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willreturn($jobParameters);
        $jobParameters->get('project_code')->willReturn('project_code');

        $projectRepository->findOneByIdentifier('project_code')->willReturn(null);

        $this->shouldThrow(\RuntimeException::class)->during('execute');
    }
}
