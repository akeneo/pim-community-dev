<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;

class OptionCodeUpdatedQueryGeneratorSpec extends ObjectBehavior
{
    function let(NamingUtility $namingUtility)
    {
        $this->beConstructedWith($namingUtility, 'Pim\Bundle\CatalogBundle\Model\AttributeOption', 'code');
    }

    function it_generates_a_query_to_update_product_select_attributes(
        $namingUtility,
        AttributeOptionInterface $blue,
        AttributeInterface $color
    ) {
        $blue->getAttribute()->willReturn($color);
        $namingUtility
            ->getAttributeNormFields($color)
            ->willReturn(['normalizedData.color-fr_FR', 'normalizedData.color-en_US']);

        $this->generateQuery($blue, 'code', 'blue', 'bleu')->shouldReturn([
            [
                ['normalizedData.color-fr_FR' => [ '$exists' => true ]],
                ['$set' => ['normalizedData.color-fr_FR.code' => 'bleu']],
                ['multiple' => true]
            ],
            [
                ['normalizedData.color-en_US' => [ '$exists' => true ]],
                ['$set' => ['normalizedData.color-en_US.code' => 'bleu']],
                ['multiple' => true]
            ]
        ]);
    }
}
