<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\AttributeNamingUtility;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

class MultipleOptionCodeUpdatedQueryGeneratorSpec extends ObjectBehavior
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
        $this->generateQuery($blue, 'code', 'blue', 'bluee')->shouldReturn([
            [
                ['normalizedData.color-fr_FR' => ['$elemMatch' => ['code' => 'blue']]],
                ['$set' => ['normalizedData.color-fr_FR.$.code' => 'bluee']],
                ['multiple' => true]
            ],
            [
                ['normalizedData.color-en_US' => ['$elemMatch' => ['code' => 'blue']]],
                ['$set' => ['normalizedData.color-en_US.$.code' => 'bluee']],
                ['multiple' => true]
            ]
        ]);
    }
}
