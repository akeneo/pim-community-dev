<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Event;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEventInterface;
use PimEnterprise\Bundle\ProductAssetBundle\JobLauncher\CommandLauncher;
use Prophecy\Argument;

class AssetEventListenerSpec extends ObjectBehavior
{
    function let(CommandLauncher $commandLauncher)
    {
        $this->beConstructedWith($commandLauncher);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEventListener');
    }
}
