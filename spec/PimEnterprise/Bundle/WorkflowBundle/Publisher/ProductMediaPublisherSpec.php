<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

class ProductMediaPublisherSpec extends ObjectBehavior
{
    function let(MediaManager $mediaManager)
    {
        $this->beConstructedWith('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia', $mediaManager);
    }

    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function it_supports_media(Model\AbstractMedia $value) {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_media(Model\AbstractMedia $media, Model\Product $product, Model\ProductValue $value) {
        $options = ['product' => $product, 'value' => $value];
        $this->publish($media, $options)->shouldReturnAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia');
    }
}
