<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\PrepareProjectCalculationTasklet;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\PreProcessingRepositoryInterface;
use Prophecy\Argument;

class PrepareProjectCalculationTaskletSpec extends ObjectBehavior
{
    function let(
        PreProcessingRepositoryInterface $preProcessingRepository,
        IdentifiableObjectRepositoryInterface $projectRepository
    ) {
        $this->beConstructedWith($preProcessingRepository, $projectRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PrepareProjectCalculationTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_is_step_execution_aware(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn(null);
    }

    function it_prepare_the_project_calculation_by_resetting_user_groups(
        $preProcessingRepository,
        $projectRepository,
        StepExecution $stepExecution,
        ProjectInterface $project,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willreturn($jobParameters);
        $jobParameters->get('project_code')->willReturn('project_code');

        $projectRepository->findOneByIdentifier('project_code')->willReturn($project);
        $preProcessingRepository->prepareProjectCalculation($project)->shouldBeCalled();

        $project->resetUserGroups()->shouldBeCalled();

        $this->execute()->shouldReturn(null);
    }
}
