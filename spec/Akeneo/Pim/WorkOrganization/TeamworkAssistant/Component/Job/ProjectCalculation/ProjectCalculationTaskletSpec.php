<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\ProjectCalculationTasklet;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProductRepositoryInterface;

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

        $stepExecution->incrementSummaryInfo('processed_products')->shouldBeCalledTimes(2);

        $objectDetacher->detach($product)->shouldBeCalled();
        $objectDetacher->detach($otherProduct)->shouldBeCalled();

        $projectSaver->save($project);

        $this->execute()->shouldReturn(null);
    }
}
