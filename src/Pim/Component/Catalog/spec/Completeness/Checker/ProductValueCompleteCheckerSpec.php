<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;

class ProductValueCompleteCheckerSpec extends ObjectBehavior
{
    function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    function it_tells_the_value_is_not_complete_by_default(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    function it_supports_non_localisable_and_non_scopable_value(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_supports_localisable_value(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn($locale);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_supports_locale_specific_value(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn($locale);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(true);
        $attribute->hasLocaleSpecific($locale)->willReturn(true);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_supports_scopable_value(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn($channel);
        $value->getLocale()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_supports_scopable_and_localisable_value(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn($channel);
        $value->getLocale()->willReturn($locale);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);
    }

    function it_does_not_supports_a_locale_that_does_not_match_the_localisable_value(
        ProductValueInterface $value,
        LocaleInterface $localeValue,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn($localeValue);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(false);
    }

    function it_does_not_supports_a_locale_that_does_not_match_the_locale_specific_value(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn(null);
        $value->getLocale()->willReturn($locale);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(true);
        $attribute->hasLocaleSpecific($locale)->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(false);
    }

    function it_does_not_supports_a_channel_that_does_not_match_the_channel_value(
        ProductValueInterface $value,
        ChannelInterface $channelValue,
        ChannelInterface $channel,
        LocaleInterface $locale,
        AttributeInterface $attribute
    ) {
        $value->getScope()->willReturn($channelValue);
        $value->getLocale()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);
        $attribute->isLocaleSpecific()->willReturn(false);

        $this->supportsValue($value, $channel, $locale)->shouldReturn(false);
    }

    function it_successfully_checks_incomplete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductValueCompleteCheckerInterface $completenessChecker,
        AttributeInterface $attribute
    ) {
        $completenessChecker->supportsValue($value, $channel, $locale)->willReturn(true);
        $completenessChecker->isComplete($value, $channel, $locale)->willReturn(false);

        $this->addProductValueChecker($completenessChecker);

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    function it_successfully_checks_complete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductValueCompleteCheckerInterface $completenessChecker,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $this->addProductValueChecker($completenessChecker);

        $completenessChecker->supportsValue($value, $channel, $locale)->willReturn(true);
        $completenessChecker->isComplete($value, $channel, $locale)->willReturn(true);

        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }
}
