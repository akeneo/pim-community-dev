<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

class AttributeDeletedQueryGeneratorSpec extends ObjectBehavior
{
    function let(NamingUtility $namingUtility)
    {
        $this->beConstructedWith($namingUtility, 'Pim\Bundle\CatalogBundle\Model\Attribute', '');
    }

    function it_generates_a_query_to_update_product_with_the_deleted_attribute(
        $namingUtility,
        AttributeInterface $label
    ) {
        $namingUtility->getAttributeNormFields($label)->willReturn(['normalizedData.label-en_US', 'normalizedData.label-fr_FR']);

        $this->generateQuery($label, '', '', '')->shouldReturn([
            [
                ['normalizedData.label-en_US' => [ '$exists' => true ]],
                ['$unset' => ['normalizedData.label-en_US' => '']],
                ['multiple' => true]
            ],
            [
                ['normalizedData.label-fr_FR' => [ '$exists' => true ]],
                ['$unset' => ['normalizedData.label-fr_FR' => '']],
                ['multiple' => true]
            ]
        ]);
    }
}
