<?php

namespace spec\Akeneo\Component\Classification\Factory;

use PhpSpec\ObjectBehavior;

class CategoryFactorySpec extends ObjectBehavior
{
    const CATEGORY_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Category';

    function let()
    {
        $this->beConstructedWith(self::CATEGORY_CLASS);
    }

    function it_creates_a_category()
    {
        $this->create()->shouldReturnAnInstanceOf(self::CATEGORY_CLASS);
    }
}
