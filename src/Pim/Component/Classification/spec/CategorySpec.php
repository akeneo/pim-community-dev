<?php

namespace spec\Pim\Component\Classification;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Classification\Category;
use Pim\Component\Classification\CategoryIdentifier;
use Pim\Component\Classification\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategorySpec extends ObjectBehavior
{
    function let(CategoryIdentifier $categoryIdentifier, CategoryInterface $parentCategory, ArrayCollection $properties)
    {
        $this->beConstructedWith($categoryIdentifier, $parentCategory, $properties);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Category::class);
    }

    function it_is_a_category()
    {
        $this->shouldImplement(CategoryInterface::class);
    }

    function it_has_a_identifier($categoryIdentifier)
    {
        $this->getIdentifier()->shouldReturn($categoryIdentifier);
    }

    function it_has_a_parent_category($parentCategory)
    {
        $this->getParent()->shouldReturn($parentCategory);
    }

    function it_has_a_collection_of_properties($properties)
    {
        $this->getProperties()->shouldReturn($properties);
    }
}
