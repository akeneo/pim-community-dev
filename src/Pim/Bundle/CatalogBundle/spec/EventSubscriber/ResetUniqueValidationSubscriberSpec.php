<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Validator\UniqueAxesCombinationSet;
use Pim\Component\Catalog\Validator\UniqueValuesSet;

class ResetUniqueValidationSubscriberSpec extends ObjectBehavior
{
    function let(UniqueValuesSet $uniqueValueSet, UniqueAxesCombinationSet $uniqueAxesCombinationSet)
    {
        $this->beConstructedWith($uniqueValueSet, $uniqueAxesCombinationSet);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\EventSubscriber\ResetUniqueValidationSubscriber');
    }

    function it_should_reset_unique_value_set($uniqueValueSet, $uniqueAxesCombinationSet)
    {
        $uniqueValueSet->reset()->shouldBeCalled();
        $uniqueAxesCombinationSet->reset()->shouldBeCalled();

        $this->onAkeneoStoragePostsaveall();
    }
}
