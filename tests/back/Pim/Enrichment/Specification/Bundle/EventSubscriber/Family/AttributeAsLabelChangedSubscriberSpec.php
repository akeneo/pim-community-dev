<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family\AttributeAsLabelChangedSubscriber;
use PhpSpec\ObjectBehavior;

class AttributeAsLabelChangedSubscriberSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeAsLabelChangedSubscriber::class);
    }
}
