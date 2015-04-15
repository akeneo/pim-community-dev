<?php

namespace spec\Pim\Bundle\EnrichBundle\Factory;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use PhpSpec\ObjectBehavior;

class MassEditConfigurationFactorySpec extends ObjectBehavior
{
    const MASS_EDIT_JOB_CONFIGURATION_CLASS = 'Pim\Bundle\EnrichBundle\Entity\MassEditJobConfiguration';

    function let()
    {
        $this->beConstructedWith(self::MASS_EDIT_JOB_CONFIGURATION_CLASS);
    }

    function it_creates_a_mass_edit_configuration(JobExecution $jobExecution)
    {
        $this->create($jobExecution, ['configuration'])->shouldReturnAnInstanceOf(self::MASS_EDIT_JOB_CONFIGURATION_CLASS);
    }
}
