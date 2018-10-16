<?php

namespace Specification\Akeneo\Asset\Bundle\Event;

use Akeneo\Asset\Bundle\Event\AssetEvent;
use PhpSpec\ObjectBehavior;
use Akeneo\Asset\Component\Model\AssetInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AssetEventSpec extends ObjectBehavior
{
    public function let(AssetInterface $assetInterface)
    {
        $this->beConstructedWith($assetInterface, []);
    }

    public function it_can_be_initialized()
    {
        $this->shouldHaveType(AssetEvent::class);
    }

    public function it_is_a_generic_event()
    {
        $this->shouldHaveType(GenericEvent::class);
    }
}
