<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\AttributeNamingUtility;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

class OptionDeletedQueryGeneratorSpec extends ObjectBehavior
{
    function let(AttributeNamingUtility $attributeNamingUtility)
    {
        $this->beConstructedWith($attributeNamingUtility, 'Pim\Bundle\CatalogBundle\Model\AttributeOption', 'code');
    }

    function it_generates_a_query_to_update_product_select_attributes($attributeNamingUtility, AttributeOption $blue, AbstractAttribute $color)
    {
        $blue->getAttribute()->willReturn($color);
        $attributeNamingUtility->getAttributeNormFields($color)->willReturn(['normalizedData.color-fr_FR', 'normalizedData.color-en_US']);

        $blue->getCode()->willReturn('blue');
        $this->generateQuery($blue, 'code', '', '')->shouldReturn([
            [
                ['normalizedData.color-fr_FR' => [ '$exists' => true ]],
                ['$unset' => ['normalizedData.color-fr_FR' => '']],
                ['multiple' => true]
            ],
            [
                ['normalizedData.color-en_US' => [ '$exists' => true ]],
                ['$unset' => ['normalizedData.color-en_US' => '']],
                ['multiple' => true]
            ]
        ]);
    }
}
