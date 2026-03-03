<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\BatchQueue\Factory;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ExportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ImportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class JobExecutionMessageFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            [
                UiJobExecutionMessage::class => ['mass_edit', 'mass_delete'],
                ImportJobExecutionMessage::class => ['import'],
                ExportJobExecutionMessage::class => ['export', 'quick_export'],
            ],
            DataMaintenanceJobExecutionMessage::class
        );
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(JobExecutionMessageFactory::class);
    }

    function it_builds_an_ui_job_execution_message(JobInstance $jobInstance)
    {
        $jobInstance->getType()->willReturn('mass_delete');

        $jobExecutionMessage = $this->buildFromJobInstance($jobInstance, 1, []);
        $jobExecutionMessage->shouldBeAnInstanceOf(UiJobExecutionMessage::class);
        $jobExecutionMessage->getJobExecutionId()->shouldBe(1);
        $jobExecutionMessage->getTenantId()->shouldBe(null);
    }

    function it_builds_an_export_job_execution_message(JobInstance $jobInstance)
    {
        $jobInstance->getType()->willReturn('quick_export');

        $jobExecutionMessage = $this->buildFromJobInstance($jobInstance, 2, []);
        $jobExecutionMessage->shouldBeAnInstanceOf(ExportJobExecutionMessage::class);
        $jobExecutionMessage->getJobExecutionId()->shouldBe(2);
    }

    function it_builds_a_backend_job_execution_message(JobInstance $jobInstance)
    {
        $jobInstance->getType()->willReturn('other');

        $jobExecutionMessage = $this->buildFromJobInstance($jobInstance, 3, []);
        $jobExecutionMessage->shouldBeAnInstanceOf(DataMaintenanceJobExecutionMessage::class);
        $jobExecutionMessage->getJobExecutionId()->shouldBe(3);
    }

    function it_builds_an_ui_job_execution_message_from_normalized()
    {
        $jobExecutionMessage = $this->buildFromNormalized(
            [
                'id' => '30e8008d-48dc-4430-97e1-6f67a5c420e9',
                'job_execution_id' => 10,
                'created_time' => '2021-03-08T15:37:23+01:00',
                'updated_time' => null,
                'options' => ['option1' => 'value1'],
            ],
            UiJobExecutionMessage::class
        );
        $jobExecutionMessage->shouldBeAnInstanceOf(UiJobExecutionMessage::class);
        $jobExecutionMessage->getId()->shouldBeLike(Uuid::fromString('30e8008d-48dc-4430-97e1-6f67a5c420e9'));
        $jobExecutionMessage->getJobExecutionId()->shouldBe(10);
        $jobExecutionMessage->getCreateTime()->shouldBeLike(new \DateTime('2021-03-08T15:37:23+01:00'));
        $jobExecutionMessage->getUpdatedTime()->shouldBeNull();
        $jobExecutionMessage->getOptions()->shouldBe(['option1' => 'value1']);
    }

    function it_builds_an_import_job_execution_message_from_normalized()
    {
        $jobExecutionMessage = $this->buildFromNormalized(
            [
                'id' => 'a57380fc-ee3b-4bd2-94e6-c3ead13c32a7',
                'job_execution_id' => 10,
                'created_time' => '2021-03-08T15:37:23+01:00',
                'updated_time' => '2021-03-09T15:37:23+01:00',
                'options' => ['option1' => 'value1'],
            ],
            ImportJobExecutionMessage::class
        );
        $jobExecutionMessage->shouldBeAnInstanceOf(ImportJobExecutionMessage::class);
        $jobExecutionMessage->getId()->shouldBeLike(Uuid::fromString('a57380fc-ee3b-4bd2-94e6-c3ead13c32a7'));
        $jobExecutionMessage->getJobExecutionId()->shouldBe(10);
        $jobExecutionMessage->getCreateTime()->shouldBeLike(new \DateTime('2021-03-08T15:37:23+01:00'));
        $jobExecutionMessage->getUpdatedTime()->shouldBeLike(new \DateTime('2021-03-09T15:37:23+01:00'));
        $jobExecutionMessage->getOptions()->shouldBe(['option1' => 'value1']);
    }

    function it_builds_a_backend_job_execution_message_from_normalized(
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getType()->willReturn('other');

        $jobExecutionMessage = $this->buildFromNormalized(
            [
                'id' => 'a57380fc-ee3b-4bd2-94e6-c3ead13c32a7',
                'job_execution_id' => 10,
                'created_time' => '2021-03-08T15:37:23+01:00',
                'updated_time' => '2021-03-09T15:37:23+01:00',
                'options' => [],
            ],
            null
        );
        $jobExecutionMessage->shouldBeAnInstanceOf(DataMaintenanceJobExecutionMessage::class);
        $jobExecutionMessage->getId()->shouldBeLike(Uuid::fromString('a57380fc-ee3b-4bd2-94e6-c3ead13c32a7'));
        $jobExecutionMessage->getJobExecutionId()->shouldBe(10);
        $jobExecutionMessage->getCreateTime()->shouldBeLike(new \DateTime('2021-03-08T15:37:23+01:00'));
        $jobExecutionMessage->getUpdatedTime()->shouldBeLike(new \DateTime('2021-03-09T15:37:23+01:00'));
        $jobExecutionMessage->getOptions()->shouldBe([]);
    }
}
