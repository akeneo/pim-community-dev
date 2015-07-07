<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class MediaPublisherSpec extends ObjectBehavior
{
    function let(MediaManager $mediaManager)
    {
        $this->beConstructedWith('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia', $mediaManager);
    }

    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function it_supports_media(ProductMediaInterface $value)
    {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_media(ProductMediaInterface $media, ProductInterface $product, ProductValueInterface $value)
    {
        $options = ['product' => $product, 'value' => $value];
        $this
            ->publish($media, $options)
            ->shouldReturnAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia');
    }
}
