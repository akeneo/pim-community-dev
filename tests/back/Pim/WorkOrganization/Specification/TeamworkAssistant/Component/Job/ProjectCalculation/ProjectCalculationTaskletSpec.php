<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\ProjectCalculationTasklet;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;

class ProjectCalculationTaskletSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        CalculationStepInterface $chainCalculationStep,
        SaverInterface $projectSaver,
        ObjectDetacherInterface $objectDetacher,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->beConstructedWith(
            $productRepository,
            $projectRepository,
            $chainCalculationStep,
            $projectSaver,
            $objectDetacher,
            $cacheClearer
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
        ProductRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        CalculationStepInterface $chainCalculationStep,
        SaverInterface $projectSaver,
        ObjectDetacherInterface $objectDetacher,
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

    function it_clears_the_cache_during_project_calculation(
        ProductRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        CalculationStepInterface $chainCalculationStep,
        SaverInterface $projectSaver,
        ObjectDetacherInterface $objectDetacher,
        EntityManagerClearerInterface $cacheClearer,
        StepExecution $stepExecution,
        ProjectInterface $project,
        ProductInterface $product,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willreturn($jobParameters);
        $jobParameters->get('project_code')->willReturn('project_code');
        $projectRepository->findOneByIdentifier('project_code')->willReturn($project);
        $productRepository->findByProject($project)->willReturn(array_fill(0, 1001, $product));

        $chainCalculationStep->execute($product, $project)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('processed_products')->shouldBeCalledTimes(1001);
        $objectDetacher->detach($product)->shouldBeCalled();
        $cacheClearer->clear()->shouldBeCalled();

        $projectSaver->save($project);

        $this->execute()->shouldReturn(null);
    }
}
