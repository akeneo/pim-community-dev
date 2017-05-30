<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Validator\UniqueValuesSet;

class ResetUniqueValidationSubscriberSpec extends ObjectBehavior
{
    function let(UniqueValuesSet $uniqueValueSet)
    {
        $this->beConstructedWith($uniqueValueSet);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\EventSubscriber\ResetUniqueValidationSubscriber');
    }

    function it_should_reset_unique_value_set($uniqueValueSet)
    {
        $uniqueValueSet->reset()->shouldBeCalled();
        $this->onAkeneoStoragePostsaveall();
    }
}
