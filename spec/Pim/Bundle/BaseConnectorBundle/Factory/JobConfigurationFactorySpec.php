<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Factory;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use PhpSpec\ObjectBehavior;

class JobConfigurationFactorySpec extends ObjectBehavior
{
    const JOB_CONFIGURATION_CLASS = 'Pim\Bundle\BaseConnectorBundle\Model\JobConfiguration';

    function let()
    {
        $this->beConstructedWith(self::JOB_CONFIGURATION_CLASS);
    }

    function it_creates_a_mass_edit_configuration(JobExecution $jobExecution)
    {
        $this->create($jobExecution, ['configuration'])->shouldReturnAnInstanceOf(self::JOB_CONFIGURATION_CLASS);
    }
}
