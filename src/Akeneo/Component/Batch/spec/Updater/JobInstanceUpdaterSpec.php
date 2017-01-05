<?php

namespace spec\Akeneo\Component\Batch\Updater;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JobInstanceUpdaterSpec extends ObjectBehavior
{
    function let(JobParametersFactory $jobParametersFactory, JobRegistry $jobRegistry)
    {
        $this->beConstructedWith($jobParametersFactory, $jobRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Batch\Updater\JobInstanceUpdater');
    }

    function it_is_object_updater()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface');
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

    function it_throw_an_exception_id_it_is_not_a_job_instance()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                'Akeneo\Component\Batch\Model\JobInstance'
            )
        )->during('update', [new \stdClass(), []]);
    }
}
