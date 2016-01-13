<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Model;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;

class ProductDraftSpec extends ObjectBehavior
{
    function it_removes_category_id()
    {
        $this->setCategoryIds([4, 8, 15, 16, 23, 42]);
        $this->removeCategoryId(15);

        $this->getCategoryIds()->shouldReturn([4, 8, 16, 23, 42]);
    }

    function it_ignores_unknown_value_while_remove_category_id()
    {
        $this->setCategoryIds([4, 8, 15, 16, 23, 42]);
        $this->removeCategoryId(17);

        $this->getCategoryIds()->shouldReturn([4, 8, 15, 16, 23, 42]);
    }

    function it_throws_an_exception_if_we_try_to_get_changes_for_a_scopable_attribute_without_scope(
        AttributeInterface $attribute,
        LocaleInterface $locale
    ) {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getCode()->willReturn('name');

        $locale->getCode()->willReturn('en_US');

        $exc = new \LogicException('Trying to get changes for the scopable attribute "name" without scope.');
        $this->shouldThrow($exc)->during('getChangeForAttribute', [$attribute, null, $locale]);
    }

    function it_throws_an_exception_if_we_try_to_get_changes_for_a_localizable_attribute_without_locale(
        AttributeInterface $attribute,
        ChannelInterface $channel
    ) {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getCode()->willReturn('name');

        $channel->getCode()->willReturn('ecommerce');

        $exc = new \LogicException('Trying to get changes for the localizable attribute "name" without locale.');
        $this->shouldThrow($exc)->during('getChangeForAttribute', [$attribute, $channel, null]);
    }

    function it_gives_changes_for_attribute(
        AttributeInterface $attribute,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getCode()->willReturn('name');

        $channel->getCode()->willReturn('ecommerce');
        $locale->getCode()->willReturn('en_US');

        $this->setChanges(['values' => ['name' => [
            ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name'],
            ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'a french name'],
        ]]]);

        $this->getChangeForAttribute($attribute, $channel, $locale)->shouldReturn('an english name');
    }

    function it_does_not_give_changes_for_unknown_attribute(
        AttributeInterface $attribute,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getCode()->willReturn('name');

        $channel->getCode()->willReturn('ecommerce');
        $locale->getCode()->willReturn('en_US');

        $this->getChangeForAttribute($attribute, $channel, $locale)->shouldReturn(null);
    }

    function it_remove_changes_for_attribute(
        AttributeInterface $attribute,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getCode()->willReturn('name');

        $channel->getCode()->willReturn('ecommerce');
        $locale->getCode()->willReturn('en_US');

        $this->setChanges(['values' => ['name' => [
            ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name'],
            ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'a french name'],
        ]]]);

        $this->removeChangeForAttribute($attribute, $channel, $locale);
        $this->getChanges()->shouldReturn(['values' => ['name' => [
            ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'a french name']
        ]]]);
    }

    function it_remove_changes_for_attribute_and_clean(
        AttributeInterface $attribute,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getCode()->willReturn('name');

        $channel->getCode()->willReturn('ecommerce');
        $locale->getCode()->willReturn('en_US');

        $this->setChanges(['values' => ['name' => [
            ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name'],
        ]]]);

        $this->removeChangeForAttribute($attribute, $channel, $locale);
        $this->getChanges()->shouldReturn(['values' => []]);
    }

    function it_has_no_changes_if_changes_empty()
    {
        $this->hasChanges()->shouldReturn(false);
    }

    function it_has_no_changes_if_changes_values_empty()
    {
        $this->setChanges(['values' => []]);
        $this->hasChanges()->shouldReturn(false);
    }

    function it_has_no_changes_if_changes_values_are_not_empty()
    {
        $this->setChanges(['values' => ['name' => [
            ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name'],
        ]]]);

        $this->hasChanges()->shouldReturn(true);
    }
}
