<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\Model;

use Akeneo\Pim\Enrichment\Product\Domain\Model\ProductIdentifier;
use PhpSpec\ObjectBehavior;

class ProductIdentifierSpec extends ObjectBehavior
{
    function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_constructed_from_a_string()
    {
        $this->beConstructedThrough('fromString', ['foo']);
        $this->shouldHaveType(ProductIdentifier::class);
        $this->asString()->shouldReturn('foo');
    }
}
