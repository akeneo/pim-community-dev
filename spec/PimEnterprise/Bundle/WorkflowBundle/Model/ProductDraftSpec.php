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

    function it_gives_changes_for_attribute() {
        $this->setChanges(['values' => ['name' => [
            ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name'],
            ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'a french name'],
        ]]]);

        $this->getChange('name', 'en_US', 'ecommerce')->shouldReturn('an english name');
    }

    function it_does_not_give_changes_for_unknown_attribute() {
        $this->getChange('name', 'en_US', 'ecommerce')->shouldReturn(null);
    }

    function it_remove_changes_for_attribute() {
        $this->setChanges(['values' => ['name' => [
            ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name'],
            ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'a french name'],
        ]]]);

        $this->removeChange('name', 'en_US', 'ecommerce');
        $this->getChanges()->shouldReturn(['values' => ['name' => [
            ['scope' => 'ecommerce', 'locale' => 'fr_FR', 'data' => 'a french name']
        ]]]);
    }

    function it_remove_changes_for_attribute_and_clean() {
        $this->setChanges(['values' => ['name' => [
            ['scope' => 'ecommerce', 'locale' => 'en_US', 'data' => 'an english name'],
        ]]]);

        $this->removeChange('name', 'en_US', 'ecommerce');
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
