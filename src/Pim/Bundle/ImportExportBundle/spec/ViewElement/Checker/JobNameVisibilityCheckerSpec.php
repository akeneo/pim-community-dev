<?php

namespace spec\Pim\Bundle\ImportExportBundle\ViewElement\Checker;

use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JobNameVisibilityCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\ViewElement\Checker\JobNameVisibilityChecker');
    }

    function it_is_a_visibility_checker()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\ViewElement\Checker\VisibilityCheckerInterface');
    }

    function it_requires_the_property_in_the_configuration()
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('The "job_names" should be provided in the configuration.'))
            ->duringIsVisible();
    }

    function it_checks_if_the_given_job_code_exists_in_the_context(JobInstance $jobInstance)
    {
        $jobInstance->getAlias()->willReturn('a_job_code');
        $this->isVisible(['job_names' => ['a_job_code']], ['jobInstance' => $jobInstance])->shouldReturn(true);

        $jobInstance->getAlias()->willReturn('another_job_code');
        $this->isVisible(['job_names' => ['a_job_code']], ['jobInstance' => $jobInstance])->shouldReturn(false);
    }

    function it_thows_exception_if_no_job_instance_is_provided()
    {
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringIsVisible([['job_names' => ['a_job_code']], ['jobInstance' => null]]);
    }
}
