<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Event;

use Akeneo\Component\Console\CommandLauncher;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
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

    function it_can_generate_variations_files($commandLauncher, AssetInterface $asset)
    {
        $assetEvent = new AssetEvent(null);

        $commandLauncher->execute("pimee:asset:generate-missing-variation-files", true)->shouldBeCalled();

        $this->onAssetFilesUploaded($assetEvent)->shouldReturn($assetEvent);

        $asset->getCode()->willReturn('foo');
        $assetEvent = new AssetEvent($asset->getWrappedObject());

        $commandLauncher->execute("pimee:asset:generate-missing-variation-files --asset=foo", false)->shouldBeCalled();

        $this->onAssetFilesUploaded($assetEvent)->shouldReturn($assetEvent);
    }
}
