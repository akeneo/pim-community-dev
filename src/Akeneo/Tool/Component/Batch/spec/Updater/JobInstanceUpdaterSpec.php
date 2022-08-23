<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\Batch\Updater;

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
    function let(
        JobInstance $jobInstance,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        UpsertRunningUser $upsertRunningUser
    ) {
        $jobInstance->getJobName()->willReturn('xlsx_product_import');

        $this->beConstructedWith($jobParametersFactory, $jobRegistry, $upsertRunningUser);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JobInstanceUpdater::class);
    }

    function it_is_object_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_an_job_instance(
        $jobParametersFactory,
        $jobRegistry,
        JobInstance $jobInstance,
        JobInterface $job,
        JobParameters $jobParameters
    ) {
        $jobInstance->getJobName()->willReturn('fixtures_currency_csv');
        $jobRegistry->get('fixtures_currency_csv')->willReturn($job);
        $jobParametersFactory->create($job, ['filePath' => 'currencies.csv'])->willReturn($jobParameters);
        $jobParameters->all()->willReturn(['filePath' => 'currencies.csv']);

        $jobInstance->isScheduled()->willReturn(false);
        $jobInstance->setJobName('fixtures_currency_csv')->shouldBeCalled();
        $jobInstance->setCode('fixtures_currency_csv')->shouldBeCalled();
        $jobInstance->setConnector('Data fixtures')->shouldBeCalled();
        $jobInstance->setLabel('Currencies data fixtures')->shouldBeCalled();
        $jobInstance->setRawParameters([
            'filePath' => 'currencies.csv',
        ])->shouldBeCalled();
        $jobInstance->setType('type')->shouldBeCalled();

        $this->update($jobInstance, [
            'connector' => 'Data fixtures',
            'alias' => 'fixtures_currency_csv',
            'label' => 'Currencies data fixtures',
            'type' => 'type',
            'configuration' => [
                'filePath' => 'currencies.csv',
            ],
            'code' => 'fixtures_currency_csv',
        ]);
    }

    function it_throws_an_exception_if_it_is_not_a_job_instance()
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
    ) {
        $automation = [
            'cron_expression' => '0 */8 * * *',
            'running_user_groups' => ['IT Support'],
        ];

        $jobInstance->setScheduled(false)->shouldBeCalled();
        $jobInstance->setAutomation($automation)->shouldBeCalled();
        $jobInstance->isScheduled()->willReturn(false);
        $upsertRunningUser->execute('xlsx_product_import', ['IT Support'])->shouldNotBeCalled();

        $this->update($jobInstance, [
            'scheduled' => false,
            'automation' => $automation,
        ]);
    }
}
