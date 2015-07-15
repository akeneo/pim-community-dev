<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;

class AssociationTypeFactorySpec extends ObjectBehavior
{
    const ASSOCIATION_TYPE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';

    function let()
    {
        $this->beConstructedWith(self::ASSOCIATION_TYPE_CLASS);
    }

    function it_creates_a_category()
    {
        $this->createAssociationType()->shouldReturnAnInstanceOf(self::ASSOCIATION_TYPE_CLASS);
    }
}
