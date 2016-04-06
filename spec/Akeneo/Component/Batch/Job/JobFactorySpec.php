<?php

namespace spec\Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobFactorySpec extends ObjectBehavior
{
    const TESTED_CLASS = 'Akeneo\Component\Batch\Job\Job';

    function let(EventDispatcherInterface $dispatcher, JobRepositoryInterface $repository)
    {
        $this->beConstructedWith($dispatcher, $repository);
    }

    function it_creates_job()
    {
        $this->createJob('myJobTitle')->shouldReturnAnInstanceOf(self::TESTED_CLASS);
    }
}
