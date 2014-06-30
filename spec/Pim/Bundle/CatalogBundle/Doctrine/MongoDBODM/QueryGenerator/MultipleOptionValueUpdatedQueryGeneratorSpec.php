<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\AttributeNamingUtility;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

class MultipleOptionValueUpdatedQueryGeneratorSpec extends ObjectBehavior
{
    function let(AttributeNamingUtility $attributeNamingUtility)
    {
        $this->beConstructedWith($attributeNamingUtility, 'Pim\Bundle\CatalogBundle\Model\AttributeOptionValue', 'value');
    }

    function it_generates_a_query_to_update_product_select_attributes($attributeNamingUtility, AttributeOptionValue $bleu, AttributeOption $blue, AbstractAttribute $color)
    {
        $bleu->getOption()->willReturn($blue);
        $bleu->getLocale()->willReturn('fr_FR');
        $blue->getAttribute()->willReturn($color);
        $attributeNamingUtility->getAttributeNormFields($color)->willReturn(['normalizedData.color-fr_FR', 'normalizedData.color-en_US']);

        $blue->getCode()->willReturn('blue');
        $this->generateQuery($bleu, 'value', 'Bleu', 'Bleus')->shouldReturn([
            [
                ['normalizedData.color-fr_FR' => ['$elemMatch' => ['code' => 'blue']]],
                ['$set' => ['normalizedData.color-fr_FR.$.optionValues.fr_FR.value' => 'Bleus']],
                ['multiple' => true]
            ],
            [
                ['normalizedData.color-en_US' => ['$elemMatch' => ['code' => 'blue']]],
                ['$set' => ['normalizedData.color-en_US.$.optionValues.fr_FR.value' => 'Bleus']],
                ['multiple' => true]
            ]
        ]);
    }
}
