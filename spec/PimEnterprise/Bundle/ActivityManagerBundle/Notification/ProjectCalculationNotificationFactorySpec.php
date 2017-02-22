<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Localization\Presenter\DatePresenter;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCalculationNotificationFactory;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;

class ProjectCalculationNotificationFactorySpec extends ObjectBehavior
{
    function let(ProjectRepositoryInterface $projectRepository, DatePresenter $datePresenter)
    {
        $this->beConstructedWith(
            $projectRepository,
            $datePresenter,
            ['project_calculation'],
            'Pim\Bundle\NotificationBundle\Entity\Notification'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCalculationNotificationFactory::class);
    }

    function it_supports_type()
    {
        $this->supports('project_calculation')->shouldReturn(true);
        $this->supports('import')->shouldReturn(false);
    }

    function it_creates_a_notification(
        $projectRepository,
        $datePresenter,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        BatchStatus $batchStatus,
        JobParameters $jobParameters,
        ProjectInterface $project,
        UserInterface $owner,
        LocaleInterface $locale
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getStatus()->willReturn($batchStatus);
        $jobExecution->getId()->willReturn(1);
        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('project_code')->willReturn('project_code');
        $projectRepository->findOneBy(['code' => 'project_code'])->willReturn($project);
        $project->getOwner()->willReturn($owner);
        $project->getDueDate()->willReturn('12/13/2018');
        $project->getLabel()->willReturn('project label');
        $owner->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $datePresenter->present(
            '12/13/2018',
            ['locale' => 'en_US']
        )->willReturn('12/13/2018');
        $batchStatus->isUnsuccessful()->willReturn(true);
        $jobInstance->getType()->willReturn('import');
        $jobInstance->getLabel()->willReturn('Import');

        $this->create($jobExecution)->shouldReturnAnInstanceOf('Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_throws_an_exception_if_param_is_not_a_job_exception()
    {
        $this->shouldThrow(
            new \InvalidArgumentException('Expects a Akeneo\Component\Batch\Model\JobExecution, "stdClass" provided')
        )->during('create', [new \stdClass()]);
    }
}
