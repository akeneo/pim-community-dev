<?php

namespace spec\Akeneo\Component\Batch\Updater;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class JobInstanceUpdaterSpec extends ObjectBehavior
{
    function let(JobParametersFactory $jobParametersFactory, ContainerInterface $container)
    {
        $this->beConstructedWith($jobParametersFactory, $container);
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
        $container,
        ConnectorRegistry $registry,
        JobInstance $jobInstance,
        JobInterface $job,
        JobParameters $jobParameters
    ) {
        $container->get('akeneo_batch.connectors')->willReturn($registry);
        $registry->getJob($jobInstance)->willReturn($job);
        $jobParametersFactory->create($job, ['filePath' => 'currencies.csv'])->willReturn($jobParameters);
        $jobParameters->all()->willReturn(['filePath' => 'currencies.csv']);

        $jobInstance->setAlias('fixtures_currency_csv')->shouldBeCalled();
        $jobInstance->setCode('fixtures_currency_csv')->shouldBeCalled();
        $jobInstance->setConnector('Data fixtures')->shouldBeCalled();
        $jobInstance->setLabel('Currencies data fixtures')->shouldBeCalled();
        $jobInstance->setRawConfiguration([
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
        $this->shouldThrow('\InvalidArgumentException')->during('update', [new \stdClass(), []]);
    }
}
