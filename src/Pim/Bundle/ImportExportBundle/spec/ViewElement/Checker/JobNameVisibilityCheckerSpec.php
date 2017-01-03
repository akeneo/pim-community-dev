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

    function it_checks_if_the_given_job_code_exists_in_the_context(JobInstance $jobInstance)
    {
        $this->addJobName('a_job_code');

        $jobInstance->getJobName()->willReturn('a_job_code');
        $this->isVisible([], ['jobInstance' => $jobInstance])->shouldReturn(true);

        $jobInstance->getJobName()->willReturn('another_job_code');
        $this->isVisible([], ['jobInstance' => $jobInstance])->shouldReturn(false);
    }

    function it_thows_exception_if_no_job_instance_is_provided()
    {
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringIsVisible([[], ['jobInstance' => null]]);
    }
}
