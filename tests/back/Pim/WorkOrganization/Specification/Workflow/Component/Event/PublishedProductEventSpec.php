<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Event;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\PublishedProductEvent;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PublishedProductEventSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PublishedProductEvent::class);
    }

    function it_is_an_event()
    {
        $this->shouldHaveType(Event::class);
    }

    function let(ProductInterface $product, PublishedProductInterface $published)
    {
        $this->beConstructedWith($product, $published);
    }

    function its_product_is_be_mutable($product)
    {
        $this->setProduct($product);
        $this->getProduct()->shouldReturn($product);
    }

    function its_published_product_is_be_mutable($published)
    {
        $this->setPublishedProduct($published);
        $this->getPublishedProduct()->shouldReturn($published);
    }
}
