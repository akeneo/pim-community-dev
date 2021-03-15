<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\BatchQueue\Factory;

use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\BackendJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ExportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ImportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;

class JobExecutionMessageFactorySpec extends ObjectBehavior
{
    function let(ObjectRepository $jobExecutionRepository)
    {
        $this->beConstructedWith(
            $jobExecutionRepository,
            [
                UiJobExecutionMessage::class => ['mass_edit', 'mass_delete'],
                ImportJobExecutionMessage::class => ['import'],
                ExportJobExecutionMessage::class => ['export', 'quick_export'],
            ],
            BackendJobExecutionMessage::class
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
        $jobExecutionMessage->shouldBeAnInstanceOf(BackendJobExecutionMessage::class);
        $jobExecutionMessage->getJobExecutionId()->shouldBe(3);
    }

    function it_builds_an_ui_job_execution_message_from_normalized(
        ObjectRepository $jobExecutionRepository,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $jobExecutionRepository->find(10)->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getType()->willReturn('mass_delete');

        $jobExecutionMessage = $this->buildFromNormalized([
            'id' => 5,
            'job_execution_id' => 10,
            'consumer' => 'consumer_name',
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => null,
            'options' => ['option1' => 'value1'],
        ]);
        $jobExecutionMessage->shouldBeAnInstanceOf(UiJobExecutionMessage::class);
        $jobExecutionMessage->getId()->shouldBe(5);
        $jobExecutionMessage->getJobExecutionId()->shouldBe(10);
        $jobExecutionMessage->getConsumer()->shouldBe('consumer_name');
        $jobExecutionMessage->getCreateTime()->shouldBeLike(new \DateTime('2021-03-08T15:37:23+01:00'));
        $jobExecutionMessage->getUpdatedTime()->shouldBeNull();
        $jobExecutionMessage->getOptions()->shouldBe(['option1' => 'value1']);
    }

    function it_builds_an_import_job_execution_message_from_normalized(
        ObjectRepository $jobExecutionRepository,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $jobExecutionRepository->find(10)->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getType()->willReturn('import');

        $jobExecutionMessage = $this->buildFromNormalized([
            'id' => 5,
            'job_execution_id' => 10,
            'consumer' => 'consumer_name',
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => '2021-03-09T15:37:23+01:00',
            'options' => ['option1' => 'value1'],
        ]);
        $jobExecutionMessage->shouldBeAnInstanceOf(ImportJobExecutionMessage::class);
        $jobExecutionMessage->getId()->shouldBe(5);
        $jobExecutionMessage->getJobExecutionId()->shouldBe(10);
        $jobExecutionMessage->getConsumer()->shouldBe('consumer_name');
        $jobExecutionMessage->getCreateTime()->shouldBeLike(new \DateTime('2021-03-08T15:37:23+01:00'));
        $jobExecutionMessage->getUpdatedTime()->shouldBeLike(new \DateTime('2021-03-09T15:37:23+01:00'));
        $jobExecutionMessage->getOptions()->shouldBe(['option1' => 'value1']);
    }

    function it_builds_a_backend_job_execution_message_from_normalized(
        ObjectRepository $jobExecutionRepository,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $jobExecutionRepository->find(10)->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getType()->willReturn('other');

        $jobExecutionMessage = $this->buildFromNormalized([
            'id' => null,
            'job_execution_id' => 10,
            'consumer' => null,
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => '2021-03-09T15:37:23+01:00',
            'options' => [],
        ]);
        $jobExecutionMessage->shouldBeAnInstanceOf(BackendJobExecutionMessage::class);
        $jobExecutionMessage->getId()->shouldBeNull();
        $jobExecutionMessage->getJobExecutionId()->shouldBe(10);
        $jobExecutionMessage->getConsumer()->shouldBeNull();
        $jobExecutionMessage->getCreateTime()->shouldBeLike(new \DateTime('2021-03-08T15:37:23+01:00'));
        $jobExecutionMessage->getUpdatedTime()->shouldBeLike(new \DateTime('2021-03-09T15:37:23+01:00'));
        $jobExecutionMessage->getOptions()->shouldBe([]);
    }
}
