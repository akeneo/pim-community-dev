<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;

class ProductTemplateFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Pim\Bundle\CatalogBundle\Entity\ProductTemplate');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Factory\ProductTemplateFactory');
    }

    function it_creates_product_templates()
    {
        $this->createProductTemplate()->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\ProductTemplate');
    }
}
