<?php

namespace spec\Pim\Bundle\CatalogBundle\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Category;

class ProductSpec extends ObjectBehavior
{
    function it_has_family(Family $family)
    {
        $family->getId()->willReturn(42);
        $this->setFamily($family);
        $this->getFamily()->shouldReturn($family);
        $this->getFamilyId()->shouldReturn(42);
    }

    function it_belongs_to_categories(Category $category1, Category $category2)
    {
        $this->addCategory($category1);
        $this->getCategories()->shouldHaveCount(1);
        $this->addCategory($category2);
        $this->getCategories()->shouldHaveCount(2);
    }
}
