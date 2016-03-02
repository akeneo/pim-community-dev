<?php

namespace spec\Akeneo\Bundle\BatchBundle\Step;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StepFactorySpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $dispatcher, JobRepositoryInterface $repository)
    {
        $this->beConstructedWith($dispatcher, $repository);
    }

    function it_creates_step(ItemReaderInterface $reader)
    {
        $this->createStep(
            'myStepTitle',
            'Akeneo\Component\Batch\Step\ItemStep',
            ['reader' => $reader],
            []
        )->shouldReturnAnInstanceOf('Akeneo\Component\Batch\Step\ItemStep');
    }
}