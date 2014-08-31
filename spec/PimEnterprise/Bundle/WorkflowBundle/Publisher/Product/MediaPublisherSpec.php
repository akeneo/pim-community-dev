<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

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

    function it_supports_media(AbstractProductMedia $value)
    {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_media(AbstractProductMedia $media, ProductInterface $product, AbstractProductValue $value)
    {
        $options = ['product' => $product, 'value' => $value];
        $this
            ->publish($media, $options)
            ->shouldReturnAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia');
    }
}
