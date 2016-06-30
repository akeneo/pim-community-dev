<?php

namespace spec\Akeneo\Bundle\BatchBundle\Connector;

use Akeneo\Component\Batch\Job\JobFactory;
use Akeneo\Component\Batch\Step\StepFactory;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Step\ItemStep;
use PhpSpec\ObjectBehavior;

class ConnectorRegistrySpec extends ObjectBehavior
{
    function let(JobFactory $jobFactory, StepFactory $stepFactory, ItemStep $step, Job $job)
    {
        $this->beConstructedWith($jobFactory, $stepFactory);

        $jobName = 'fixtures_category_csv';
        $jobFactory->createJob($jobName)->willReturn($job);
        $stepName = 'validation';
        $stepClass = 'Pim\\Component\\Connector\\Step\\ValidatorStep';
        $stepServices = [];
        $stepParameters = [];
        $stepFactory->createStep($stepName, $stepClass, $stepServices, $stepParameters)->willReturn($step);
        $job->addStep($stepName, $step)->shouldBeCalled();

        $connector = 'Data fixtures';
        $jobType = 'fixtures';
        $this->addStepToJob(
            $connector,
            $jobType,
            $jobName,
            $stepName,
            $stepClass,
            $stepServices,
            $stepParameters
        );
    }

    function it_provides_a_configured_job(JobInstance $jobInstance, $job)
    {
        $rawConfiguration = ['my raw conf'];
        $jobInstance->getConnector()->willReturn('Data fixtures');
        $jobInstance->getType()->willReturn('fixtures');
        $jobInstance->getJobName()->willReturn('fixtures_category_csv');
        $jobInstance->getRawConfiguration()->willReturn($rawConfiguration);
        $this->getJob($jobInstance)->shouldReturn($job);
    }

    function it_provides_the_connectors_list_from_a_job_type()
    {
        $jobType = 'fixtures';
        $this->getConnectors($jobType)->shouldReturn(['Data fixtures']);
    }

    function it_provides_the_jobs_of_a_connector($job)
    {
        $connector = 'Data fixtures';
        $jobType = 'fixtures';
        $this->getConnector($connector, $jobType)->shouldReturn(['fixtures_category_csv' => $job]);
    }

    function it_provides_the_list_of_jobs_of_a_type($job)
    {
        $jobType = 'fixtures';
        $this->getJobs($jobType)->shouldReturn(
            ['Data fixtures' => ['fixtures_category_csv' => $job]]
        );
    }
}
