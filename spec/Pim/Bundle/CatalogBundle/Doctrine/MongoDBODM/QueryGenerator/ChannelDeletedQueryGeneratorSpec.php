<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\AttributeNamingUtility;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;

class ChannelDeletedQueryGeneratorSpec extends ObjectBehavior
{
    function let(AttributeNamingUtility $attributeNamingUtility)
    {
        $this->beConstructedWith($attributeNamingUtility, 'Pim\Bundle\CatalogBundle\Model\Channel', '');
    }

    function it_generates_a_query_to_update_product_scopable_attributes($attributeNamingUtility, AbstractAttribute $label, Channel $mobile)
    {
        $attributeNamingUtility->getScopableAttributes(false)->willReturn([$label]);
        $label->getCode()->willReturn('label');

        $attributeNamingUtility->getLocaleCodes()->willReturn(['fr_FR', 'en_US']);
        $attributeNamingUtility->appendSuffixes(['normalizedData.label'], ['fr_FR', 'en_US'])->willReturn(['normalizedData.label-fr_FR', 'normalizedData.label-en_US']);
        $attributeNamingUtility->appendSuffixes(['normalizedData.label-fr_FR', 'normalizedData.label-en_US'], ['mobile'])->willReturn(['normalizedData.label-fr_FR-mobile', 'normalizedData.label-en_US-mobile']);

        $mobile->getCode()->willReturn('mobile');

        $this->generateQuery($mobile, '', '', '')->shouldReturn([
            [
                ['normalizedData.label-fr_FR-mobile' => [ '$exists' => true ]],
                ['$unset' => ['normalizedData.label-fr_FR-mobile' => '']],
                ['multiple' => true]
            ],
            [
                ['normalizedData.label-en_US-mobile' => [ '$exists' => true ]],
                ['$unset' => ['normalizedData.label-en_US-mobile' => '']],
                ['multiple' => true]
            ]
        ]);
    }
}
