<?php

namespace spec\Pim\Bundle\InstallerBundle\FixtureLoader;

use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Processor\Denormalization\Processor;
use Pim\Component\Connector\Reader\File\Yaml\Reader;
use Prophecy\Argument;
use Symfony\Component\Config\FileLocator;

class JobInstancesBuilderSpec extends ObjectBehavior
{
    function let(FileLocator $fileLocator, Reader $yamlReader, Processor $processor)
    {
        $this->beConstructedWith($fileLocator, $yamlReader, $processor, ['my/path/community/fixtures_jobs.yml']);
    }

    function it_builds_job_instances($fileLocator, $yamlReader, $processor, JobInstance $jobInstance)
    {
        $fileLocator->locate('@my/path/community/fixtures_jobs.yml')
            ->willReturn('/home/nico/project/my/path/community/fixtures_jobs.yml');
        $rawJobInstance = [
            'order' => '10',
            'connector' => 'Data fixtures',
            'alias' => 'fixtures_currency_csv',
            'label' => 'Currencies data fixtures',
            'type' => 'fixtures',
            'configuration' => [
                'filePath' => 'currencies.csv'
            ]
        ];
        $yamlReader->setStepExecution(Argument::any())->shouldBeCalled();
        $yamlReader->read()->willReturn($rawJobInstance, null);
        unset($rawJobInstance['order']);
        $processor->process($rawJobInstance)->willReturn($jobInstance);
        $jobInstances = $this->build();
        $jobInstances[0]->shouldBe($jobInstance);
        $jobInstances->shouldHaveCount(1);
    }
}
