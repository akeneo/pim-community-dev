<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;

class GroupFactorySpec extends ObjectBehavior
{
    const GROUP_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Group';

    function let()
    {
        $this->beConstructedWith(self::GROUP_CLASS);
    }

    function it_creates_a_group()
    {
        $this->createGroup()->shouldReturnAnInstanceOf(self::GROUP_CLASS);
    }

    function it_creates_a_group_with_a_type(GroupTypeInterface $type)
    {
        $this->createGroup($type)->shouldReturnAnInstanceOf(self::GROUP_CLASS);
    }
}
