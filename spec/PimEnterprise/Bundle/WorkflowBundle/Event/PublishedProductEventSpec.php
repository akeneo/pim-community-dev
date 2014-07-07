<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Event;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use Prophecy\Argument;

class PublishedProductEventSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent');
    }

    function it_should_be_an_event()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\Event');
    }

    function let(ProductInterface $product, PublishedProductInterface $published)
    {
        $this->beConstructedWith($product, $published);
    }

    function its_product_should_be_mutable($product)
    {
        $this->setProduct($product);
        $this->getProduct()->shouldReturn($product);
    }

    function its_published_product_should_be_mutable($published)
    {
        $this->setPublishedProduct($published);
        $this->getPublishedProduct()->shouldReturn($published);
    }
}
