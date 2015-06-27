<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Event;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Prophecy\Argument;

class AssetEventSpec extends ObjectBehavior
{
    public function let(AssetInterface $assetInterface)
    {
        $this->beConstructedWith($assetInterface, []);
    }

    public function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent');
    }

    public function it_is_a_generic_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\GenericEvent');
    }
}
