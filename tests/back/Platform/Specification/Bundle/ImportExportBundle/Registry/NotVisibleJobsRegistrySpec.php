<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Registry;

use Akeneo\Platform\Bundle\ImportExportBundle\Registry\NotVisibleJobsRegistry;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use PhpSpec\ObjectBehavior;

class NotVisibleJobsRegistrySpec extends ObjectBehavior
{
    function it_returns_codes_of_not_visible_jobs(
        JobInterface $jobA,
        JobInterface $jobB,
        Service $aService
    ) {
        $this->beConstructedWith([$aService, $jobA, $jobB]);

        $aService->getName()->shouldNotBeCalled();
        $jobA->getName()->willReturn('the_job_A');
        $jobB->getName()->willReturn('the_job_B');

        $this->getCodes()->shouldReturn(['the_job_A', 'the_job_B']);
    }

    function it_is_a_not_visible_job_registry()
    {
        $this->beConstructedWith([]);
        $this->shouldBeAnInstanceOf(NotVisibleJobsRegistry::class);
    }
}

class Service {
    public function getName()
    {
        return 'a service';
    }
}