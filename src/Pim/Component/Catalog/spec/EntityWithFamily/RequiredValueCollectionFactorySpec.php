<?php

namespace spec\Pim\Component\Catalog\EntityWithFamily;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;

class RequiredValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        AttributeInterface $sku,
        AttributeInterface $price,
        AttributeInterface $description,
        ChannelInterface $ecommerce,
        ChannelInterface $print,
        LocaleInterface $en_US,
        LocaleInterface $fr_FR
    ) {
        $sku->getCode()->willReturn('sku');
        $price->getCode()->willReturn('price');
        $description->getCode()->willReturn('description');
        $ecommerce->getCode()->willReturn('ecommerce');
        $print->getCode()->willReturn('print');
        $en_US->getCode()->willReturn('en_US');
        $fr_FR->getCode()->willReturn('fr_FR');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(\Pim\Component\Catalog\EntityWithFamily\RequiredValueCollectionFactory::class);
    }

    function it_creates_a_collection_from_family_requirements_for_a_channel(
        $en_US,
        $fr_FR,
        $sku,
        $description,
        $price,
        $ecommerce,
        $print,
        FamilyInterface $family,
        AttributeRequirementInterface $requirement1,
        AttributeRequirementInterface $requirement2,
        AttributeRequirementInterface $requirement3,
        AttributeRequirementInterface $requirement4,
        AttributeRequirementInterface $requirement5,
        ValueInterface $expectedValue1,
        ValueInterface $expectedValue2,
        ValueInterface $expectedValue3,
        ValueInterface $expectedValue4
    ) {
        $expectedValue1->getAttribute()->willReturn($sku);
        $expectedValue1->getScope()->willReturn(null);
        $expectedValue1->getLocale()->willReturn(null);

        $expectedValue2->getAttribute()->willReturn($description);
        $expectedValue2->getScope()->willReturn('ecommerce');
        $expectedValue2->getLocale()->willReturn('en_US');

        $expectedValue3->getAttribute()->willReturn($description);
        $expectedValue3->getScope()->willReturn('ecommerce');
        $expectedValue3->getLocale()->willReturn('fr_FR');

        $expectedValue4->getAttribute()->willReturn($price);
        $expectedValue4->getScope()->willReturn(null);
        $expectedValue4->getLocale()->willReturn(null);

        $family->getAttributeRequirements()->willReturn([$requirement1, $requirement2, $requirement3, $requirement4, $requirement5]);

        $ecommerce->getLocales()->willReturn([$en_US, $fr_FR]);
        $print->getLocales()->willReturn([$en_US]);

        $sku->isScopable()->willReturn(false);
        $sku->isLocalizable()->willReturn(false);
        $sku->isLocaleSpecific()->willReturn(false);
        $description->isScopable()->willReturn(true);
        $description->isLocalizable()->willReturn(true);
        $description->isLocaleSpecific()->willReturn(false);
        $price->isScopable()->willReturn(false);
        $price->isLocalizable()->willReturn(false);
        $price->isLocaleSpecific()->willReturn(false);

        $requirement1->getAttribute()->willReturn($sku);
        $requirement1->getChannel()->willReturn($ecommerce);
        $requirement1->isRequired()->willReturn(true);

        $requirement2->getAttribute()->willReturn($description);
        $requirement2->getChannel()->willReturn($ecommerce);
        $requirement2->isRequired()->willReturn(true);

        $requirement3->getAttribute()->willReturn($sku);
        $requirement3->getChannel()->willReturn($print);
        $requirement3->isRequired()->willReturn(false);

        $requirement4->getAttribute()->willReturn($description);
        $requirement4->getChannel()->willReturn($print);
        $requirement4->isRequired()->willReturn(false);

        $requirement5->getAttribute()->willReturn($price);
        $requirement5->getChannel()->willReturn($print);
        $requirement5->isRequired()->willReturn(true);

        $expectedRequiredValuesEcommerce = $this->forChannel($family, $ecommerce);
        $expectedRequiredValuesEcommerce->count()->shouldReturn(3);
        $expectedRequiredValuesEcommerce->hasSame($expectedValue1)->shouldReturn(true);
        $expectedRequiredValuesEcommerce->hasSame($expectedValue2)->shouldReturn(true);
        $expectedRequiredValuesEcommerce->hasSame($expectedValue3)->shouldReturn(true);

        $expectedRequiredValuesPrint = $this->forChannel($family, $print);
        $expectedRequiredValuesPrint->count()->shouldReturn(1);
        $expectedRequiredValuesPrint->hasSame($expectedValue4)->shouldReturn(true);
    }
}
