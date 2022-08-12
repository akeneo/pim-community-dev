<?php

namespace spec\Akeneo\Tool\Component\Batch\Updater;

use Akeneo\Tool\Component\Batch\Clock\ClockInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\Batch\Updater\JobInstanceUpdater;
use PhpSpec\ObjectBehavior;

class JobInstanceUpdaterSpec extends ObjectBehavior
{
    public function let(JobParametersFactory $jobParametersFactory, JobRegistry $jobRegistry, ClockInterface $clock): void
    {
        $this->beConstructedWith($jobParametersFactory, $jobRegistry, $clock);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(JobInstanceUpdater::class);
    }

    public function it_is_object_updater(): void
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    public function it_updates_an_job_instance(
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        JobInstance $jobInstance,
        JobInterface $job,
        JobParameters $jobParameters
    ): void {
        $jobInstance->getJobName()->willReturn('fixtures_currency_csv');
        $jobInstance->getRawParameters()->willReturn(['storage' => ['type' => 'local', 'file_path' => 'currencies.csv']]);
        $jobRegistry->get('fixtures_currency_csv')->willReturn($job);
        $jobParametersFactory->create($job, ['storage' => ['type' => 'local', 'file_path' => 'currencies.csv']])->willReturn($jobParameters);
        $jobParameters->all()->willReturn(['storage' => ['type' => 'local', 'file_path' => 'currencies.csv']]);

        $jobInstance->setJobName('fixtures_currency_csv')->shouldBeCalled();
        $jobInstance->setCode('fixtures_currency_csv')->shouldBeCalled();
        $jobInstance->setConnector('Data fixtures')->shouldBeCalled();
        $jobInstance->setLabel('Currencies data fixtures')->shouldBeCalled();
        $jobInstance->setRawParameters(['storage' => ['type' => 'local', 'file_path' => 'currencies.csv']])->shouldBeCalled();
        $jobInstance->setType('type')->shouldBeCalled();

        $this->update($jobInstance, [
            'connector' => 'Data fixtures',
            'alias' => 'fixtures_currency_csv',
            'label' => 'Currencies data fixtures',
            'type' => 'type',
            'configuration' => [
                'storage' => ['type' => 'local', 'file_path' => 'currencies.csv']
            ],
            'code' => 'fixtures_currency_csv',
        ]);
    }

    public function it_updates_automation_setup_date_when_cron_expression_is_updated(
        JobInstance $jobInstance,
        ClockInterface $clock,
    ): void
    {
        $currentAutomation = [
            'cron_expression' => '0 0/8 * * *',
        ];

        $jobInstance->getAutomation()->willReturn($currentAutomation);
        $clock->now()->willReturn(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-12-27 07:00:00'));

        $expectedUpdatedAutomation = [
            'cron_expression' => '0 0/4 * * *',
            'setup_date' => '2022-12-27 07:00:00',
        ];

        $jobInstance->setAutomation($expectedUpdatedAutomation)->shouldBeCalled();

        $this->update($jobInstance, [
            'automation' => [
                'cron_expression' => '0 0/4 * * *',
            ]
        ]);
    }

    public function it_does_not_update_automation_setup_date_when_cron_expression_is_not_updated(
        JobInstance $jobInstance,
        ClockInterface $clock,
    ): void
    {
        $currentAutomation = [
            'cron_expression' => '0 0/4 * * *',
            'setup_date' => '2022-12-27 07:00:00',
        ];

        $jobInstance->getAutomation()->willReturn($currentAutomation);
        $clock->now()->shouldNotBeCalled();

        $expectedNotUpdatedAutomation = [
            'cron_expression' => '0 0/4 * * *',
            'setup_date' => '2022-12-27 07:00:00',
        ];

        $jobInstance->setAutomation($expectedNotUpdatedAutomation)->shouldBeCalled();

        $this->update($jobInstance, [
            'automation' => [
                'cron_expression' => '0 0/4 * * *',
            ]
        ]);
    }

    public function it_throw_an_exception_id_it_is_not_a_job_instance(): void
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                JobInstance::class
            )
        )->during('update', [new \stdClass(), []]);
    }
}
