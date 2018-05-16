<?php

namespace spec\Pim\Component\Catalog\EntityWithFamily;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\EntityWithFamily\RequiredValue;
use Pim\Component\Catalog\Model\AttributeInterface;

class RequiredValueSpec extends ObjectBehavior
{
    function it_creates_a_non_scopable_non_localizable_non_locale_specific_required_value(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->shouldNotThrow(\Exception::class)
            ->during('__construct', [$attribute, null, null]);
    }

    function it_creates_a_scopable_non_localizable_non_locale_specific_required_value(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->shouldNotThrow(\Exception::class)
            ->during('__construct', [$attribute, 'channel', null]);
    }

    function it_creates_a_localizable_non_scopable_non_locale_specific_required_value(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->shouldNotThrow(\Exception::class)
            ->during('__construct', [$attribute, null, 'fr_FR']);
    }

    function it_creates_a_locale_specific_non_scopable_non_localizable_required_value(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(true);

        $this->shouldNotThrow(\Exception::class)
            ->during('__construct', [$attribute, null, 'fr_FR']);
    }

    function it_does_not_create_a_non_scopable_required_value_with_a_channel(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->shouldThrow(new \LogicException('The product value cannot be scoped, see attribute \'attribute_code\' configuration'))
            ->during('__construct', [$attribute, 'channel', null]);
    }

    function it_does_not_create_a_required_value_with_a_locale_if_the_attribute_is_not_localizable_nor_locale_specific(
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->shouldThrow(new \LogicException(
            "The product value cannot be localized, see attribute 'attribute_code' configuration"
        ))->during('__construct', [$attribute, null, 'fr_FR']);
    }

    function it_does_not_set_the_locale_if_the_attribute_is_not_locale_specific(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_code');
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->shouldThrow(new \LogicException(
            "The product value cannot be localized, see attribute 'attribute_code' configuration"
        ))->during('__construct', [$attribute, null, 'fr_FR']);
    }

    function let(AttributeInterface $attribute)
    {
        $attribute->getCode()->willReturn('attribute_required');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);
        $this->beConstructedWith($attribute, 'ecommerce', 'fr_FR');
    }

    function it_returns_a_null_data()
    {
        $this->getData()->shouldReturn(null);
    }

    function it_returns_the_attribute($attribute)
    {
        $this->getAttribute()->shouldReturn($attribute);
    }

    function it_returns_the_scope()
    {
        $this->getScope()->shouldReturn('ecommerce');
    }

    function it_returns_the_locale()
    {
        $this->getLocale()->shouldReturn('fr_FR');
    }

    function it_is_comparable_to_another_required_value(RequiredValue $sameValue, RequiredValue $anotherValue)
    {
        $sameValue->getData()->willReturn(null);
        $sameValue->getScope()->willReturn('ecommerce');
        $sameValue->getLocale()->willReturn('fr_FR');

        $anotherValue->getData()->willReturn('value');
        $anotherValue->getScope()->willReturn('print');
        $anotherValue->getLocale()->willReturn('en_US');

        $this->isEqual($sameValue)->shouldReturn(true);
        $this->isEqual($anotherValue)->shouldReturn(false);
    }

}
