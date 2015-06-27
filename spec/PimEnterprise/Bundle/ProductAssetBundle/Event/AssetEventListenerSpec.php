<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Event;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEvent;
use PimEnterprise\Bundle\ProductAssetBundle\Finder\AssetFinderInterface;
use PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface;
use Prophecy\Argument;

class AssetEventListenerSpec extends ObjectBehavior
{
    function let(
        AssetFinderInterface $assetFinder,
        VariationsCollectionFilesGeneratorInterface $variationsFilesGenerator
    ) {
        $this->beConstructedWith($assetFinder, $variationsFilesGenerator);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\ProductAssetBundle\Event\AssetEventListener');
    }

    function it_can_generate_variations_files($assetFinder, $variationsFilesGenerator)
    {
        $assetEvent = new AssetEvent(null);

        $assetFinder->retrieveVariationsNotGenerated(null)
            ->shouldBeCalled()
            ->willReturn([]);

        $variationsFilesGenerator->generate([], true)->shouldBeCalled();

        $this->onAssetFilesUploaded($assetEvent)->shouldReturn($assetEvent);
    }
}
