<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;

class OptionValueUpdatedQueryGeneratorSpec extends ObjectBehavior
{
    function let(NamingUtility $namingUtility)
    {
        $this->beConstructedWith($namingUtility, 'Pim\Bundle\CatalogBundle\Model\AttributeOptionValue', 'value');
    }

    function it_generates_a_query_to_update_product_select_attributes(
        $namingUtility,
        AttributeOptionValueInterface $bleu,
        AttributeOptionInterface $blue,
        AttributeInterface $color
    ) {
        $bleu->getOption()->willReturn($blue);
        $bleu->getLocale()->willReturn('fr_FR');
        $blue->getAttribute()->willReturn($color);
        $namingUtility
            ->getAttributeNormFields($color)
            ->willReturn(['normalizedData.color-fr_FR', 'normalizedData.color-en_US']);

        $blue->getCode()->willReturn('blue');
        $this->generateQuery($bleu, 'value', 'bleu', 'bleus')->shouldReturn([
            [
                ['normalizedData.color-fr_FR.code' => 'blue'],
                ['$set' => ['normalizedData.color-fr_FR.optionValues.fr_FR.value' => 'bleus']],
                ['multiple' => true]
            ],
            [
                ['normalizedData.color-en_US.code' => 'blue'],
                ['$set' => ['normalizedData.color-en_US.optionValues.fr_FR.value' => 'bleus']],
                ['multiple' => true]
            ]
        ]);
    }
}
