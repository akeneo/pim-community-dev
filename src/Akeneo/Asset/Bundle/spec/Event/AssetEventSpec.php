<?php

namespace spec\Akeneo\Asset\Bundle\Event;

use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\AssetInterface;
use Prophecy\Argument;

class AssetEventSpec extends ObjectBehavior
{
    public function let(AssetInterface $assetInterface)
    {
        $this->beConstructedWith($assetInterface, []);
    }

    public function it_can_be_initialized()
    {
        $this->shouldHaveType('Akeneo\Asset\Bundle\Event\AssetEvent');
    }

    public function it_is_a_generic_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\GenericEvent');
    }
}
