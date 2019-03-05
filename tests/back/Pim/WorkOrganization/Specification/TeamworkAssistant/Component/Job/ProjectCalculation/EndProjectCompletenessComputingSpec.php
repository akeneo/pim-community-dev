<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\EndProjectCompletenessComputing;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;

class EndProjectCompletenessComputingSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $projectRepository,
        SaverInterface $projectSaver
    ) {
        $this->beConstructedWith(
            $projectRepository,
            $projectSaver
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EndProjectCompletenessComputing::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_is_step_execution_aware(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn(null);
    }

    function it_end_the_project_completeness_computing(
        $projectRepository,
        $projectSaver,
        StepExecution $stepExecution,
        ProjectInterface $project,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willreturn($jobParameters);
        $jobParameters->get('project_code')->willReturn('project_code');
        $projectRepository->findOneByIdentifier('project_code')->willReturn($project);

        $project->endCompletenessComputing()->shouldBeCalled();

        $projectSaver->save($project)->shouldBeCalled();

        $this->execute()->shouldReturn(null);
    }
}
