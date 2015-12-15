<?php

namespace spec\Akeneo\Bundle\BatchBundle\Connector;

use Akeneo\Bundle\BatchBundle\Job\JobFactory;
use Akeneo\Bundle\BatchBundle\Step\StepFactory;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Step\ItemStep;
use PhpSpec\ObjectBehavior;

class ConnectorRegistrySpec extends ObjectBehavior
{
    function let(JobFactory $jobFactory, StepFactory $stepFactory, ItemStep $step, Job $job)
    {
        $this->beConstructedWith($jobFactory, $stepFactory);

        $jobTitle = 'pim_installer.jobs.fixtures_category_csv.title';
        $jobFactory->createJob($jobTitle)->willReturn($job);
        $stepTitle = 'pim_connector.jobs.fixtures_category_csv.validation.title';
        $stepClass = 'Pim\\Component\\Connector\\Step\\ValidatorStep';
        $stepServices = [];
        $stepParameters = [];
        $stepFactory->createStep($stepTitle, $stepClass, $stepServices, $stepParameters)->willReturn($step);
        $job->addStep($stepTitle, $step)->shouldBeCalled();

        $connector = 'Data fixtures';
        $jobType = 'fixtures';
        $jobAlias = 'fixtures_category_csv';
        $this->addStepToJob(
            $connector,
            $jobType,
            $jobAlias,
            $jobTitle,
            $stepTitle,
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
        $jobInstance->getAlias()->willReturn('fixtures_category_csv');
        $jobInstance->getRawConfiguration()->willReturn($rawConfiguration);

        $job->setConfiguration($rawConfiguration)->shouldBeCalled();
        $jobInstance->setJob($job)->shouldBeCalled();

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
