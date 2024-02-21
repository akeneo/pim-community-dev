<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use PhpSpec\ObjectBehavior;

class AdditionalPropertySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('name', 'property');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AdditionalProperty::class);
    }

    function it_has_a_property_name()
    {
        $this->name()->shouldReturn('name');
    }

    function it_has_a_property_value()
    {
        $this->value()->shouldReturn('property');
    }
}
