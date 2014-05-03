<?php

namespace spec\Pim\Bundle\CatalogBundle\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Entity\Family;

class ProductSpec extends ObjectBehavior
{
    function it_has_family(Family $family)
    {
        $this->setFamily($family);
        $this->getFamily()->shouldReturn($family);
    }
}
