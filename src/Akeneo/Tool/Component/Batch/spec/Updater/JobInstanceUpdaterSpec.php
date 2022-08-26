<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\Batch\Updater;

use Akeneo\Tool\Component\Batch\Clock\ClockInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement\UpsertRunningUser;
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
    public function let(
        JobInstance $jobInstance,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        UpsertRunningUser $upsertRunningUser,
        ClockInterface $clock
    ) {
        $jobInstance->getJobName()->willReturn('xlsx_product_import');

        $this->beConstructedWith($jobParametersFactory, $jobRegistry, $upsertRunningUser, $clock);
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

        $jobInstance->isScheduled()->willReturn(false);
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
            'cron_expression' => '0 */8 * * *',
        ];

        $jobInstance->isScheduled()->willReturn(false);
        $jobInstance->getAutomation()->willReturn($currentAutomation);
        $clock->now()->willReturn(\DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, '2022-12-27T07:00:00+00:00'));

        $expectedUpdatedAutomation = [
            'cron_expression' => '0 */4 * * *',
            'setup_date' => '2022-12-27T07:00:00+00:00',
            'last_execution_date' => null,
        ];

        $jobInstance->setAutomation($expectedUpdatedAutomation)->shouldBeCalled();

        $this->update($jobInstance, [
            'automation' => [
                'cron_expression' => '0 */4 * * *',
            ]
        ]);
    }

    public function it_does_not_update_automation_setup_date_when_cron_expression_is_not_updated(
        JobInstance $jobInstance,
        ClockInterface $clock,
    ): void
    {
        $currentAutomation = [
            'cron_expression' => '0 */4 * * *',
            'setup_date' => '2022-12-27T07:00:00+00:00',
            'last_execution_date' => null,
        ];

        $jobInstance->isScheduled()->willReturn(false);
        $jobInstance->getAutomation()->willReturn($currentAutomation);
        $clock->now()->shouldNotBeCalled();

        $expectedNotUpdatedAutomation = [
            'cron_expression' => '0 */4 * * *',
            'setup_date' => '2022-12-27T07:00:00+00:00',
            'last_execution_date' => null,
        ];

        $jobInstance->setAutomation($expectedNotUpdatedAutomation)->shouldBeCalled();

        $this->update($jobInstance, [
            'automation' => [
                'cron_expression' => '0 */4 * * *',
            ]
        ]);
    }

    public function it_does_nothing_when_automation_is_null(
        JobInstance $jobInstance,
    ): void
    {
        $currentAutomation = null;

        $jobInstance->isScheduled()->willReturn(false);
        $jobInstance->getAutomation()->willReturn($currentAutomation);
        $jobInstance->setAutomation($currentAutomation)->shouldBeCalled();

        $this->update($jobInstance, [
            'automation' => null,
        ]);
    }

    public function it_throws_an_exception_if_it_is_not_a_job_instance()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                JobInstance::class
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_upserts_an_user_when_job_is_scheduled(
        JobInstance $jobInstance,
        UpsertRunningUser $upsertRunningUser,
    ) {
        $automation = [
            'cron_expression' => '0 */8 * * *',
            'setup_date' => '2022-12-27T07:00:00+00:00',
            'last_execution_date' => null,
            'running_user_groups' => ['IT Support'],
        ];

        $jobInstance->getCode()->willReturn('xlsx_product_import');
        $jobInstance->setScheduled(true)->shouldBeCalled();
        $jobInstance->setAutomation($automation)->shouldBeCalled();
        $jobInstance->isScheduled()->willReturn(true);
        $jobInstance->getAutomation()->willReturn($automation);
        $upsertRunningUser->execute('xlsx_product_import', ['IT Support'])->shouldBeCalledOnce();

        $this->update($jobInstance, [
            'scheduled' => true,
            'automation' => $automation,
        ]);
    }

    function it_does_not_upsert_an_user_when_job_is_not_scheduled(
        JobInstance $jobInstance,
        UpsertRunningUser $upsertRunningUser,
        ClockInterface $clock
    ) {
        $automation = [
            'cron_expression' => '0 */8 * * *',
            'setup_date' => '2022-12-27T07:00:00+00:00',
            'last_execution_date' => null,
            'running_user_groups' => ['IT Support'],
        ];

        $jobInstance->setScheduled(false)->shouldBeCalled();
        $jobInstance->setAutomation($automation)->shouldBeCalled();
        $jobInstance->isScheduled()->willReturn(false);
        $jobInstance->getAutomation()->willReturn($automation);
        $clock->now()->willReturn(\DateTimeImmutable::createFromFormat(\DateTimeImmutable::ATOM, '2022-12-27T07:00:00+00:00'));
        $upsertRunningUser->execute('xlsx_product_import', ['IT Support'])->shouldNotBeCalled();

        $this->update($jobInstance, [
            'scheduled' => false,
            'automation' => $automation,
        ]);
    }
}
