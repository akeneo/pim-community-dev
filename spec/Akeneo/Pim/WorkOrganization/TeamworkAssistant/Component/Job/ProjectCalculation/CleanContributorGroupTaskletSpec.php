<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\LocaleAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CleanContributorGroupTasklet;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;

class CleanContributorGroupTaskletSpec extends ObjectBehavior
{
    function let(
        LocaleAccessRepository $localeAccessRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        SaverInterface $projectSaver
    ) {
        $this->beConstructedWith(
            $localeAccessRepository,
            $projectRepository,
            $projectSaver
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CleanContributorGroupTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_is_step_execution_aware(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution)->shouldReturn(null);
    }

    function it_cleans_the_user_group_depending_the_local_permission(
        $localeAccessRepository,
        $projectRepository,
        $projectSaver,
        StepExecution $stepExecution,
        ProjectInterface $project,
        JobParameters $jobParameters,
        ArrayCollection $userGroup,
        Group $marketingGroup,
        Group $technicalGroup,
        LocaleInterface $locale,
        \Iterator $iterator
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willreturn($jobParameters);
        $jobParameters->get('project_code')->willReturn('project_code');
        $projectRepository->findOneByIdentifier('project_code')->willReturn($project);

        $project->getUserGroups()->willReturn($userGroup);
        $project->getLocale()->willReturn($locale);

        $userGroup->getIterator()->willReturn($iterator);
        $iterator->rewind()->shouldBeCalled();
        $iterator->valid()->willReturn(true, true, false);
        $iterator->current()->willReturn($marketingGroup, $technicalGroup);
        $iterator->next()->shouldBeCalled();

        $localeAccessRepository->getGrantedUserGroups($locale, Attributes::EDIT_ITEMS)->willReturn([
            $marketingGroup
        ]);

        $marketingGroup->getName()->willReturn('Marketing');
        $technicalGroup->getName()->willReturn('Tech');

        $project->removeUserGroup($technicalGroup)->shouldBeCalled();

        $projectSaver->save($project)->shouldBeCalled();

        $this->execute()->shouldReturn(null);
    }

    function it_does_not_remove_group_because_there_are_not_permission_right(
        $localeAccessRepository,
        $projectRepository,
        $projectSaver,
        StepExecution $stepExecution,
        ProjectInterface $project,
        JobParameters $jobParameters,
        Group $marketingGroup,
        Group $technicalGroup,
        Group $all,
        LocaleInterface $locale
    ) {
        $this->setStepExecution($stepExecution);

        $stepExecution->getJobParameters()->willreturn($jobParameters);
        $jobParameters->get('project_code')->willReturn('project_code');
        $projectRepository->findOneByIdentifier('project_code')->willReturn($project);
        $project->getLocale()->willReturn($locale);

        $localeAccessRepository->getGrantedUserGroups($locale, Attributes::EDIT_ITEMS)->willReturn([
            $marketingGroup,
            $technicalGroup,
            $all
        ]);

        $marketingGroup->getName()->willReturn('Marketing');
        $technicalGroup->getName()->willReturn('Tech');
        $all->getName()->willReturn('All');

        $projectSaver->save($project)->shouldNotBeCalled();
        $project->getUserGroups()->shouldNotBeCalled();

        $this->execute()->shouldReturn(null);
    }
}
