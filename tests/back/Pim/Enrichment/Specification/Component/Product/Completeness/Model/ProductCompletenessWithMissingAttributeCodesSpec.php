<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use PhpSpec\ObjectBehavior;

class ProductCompletenessWithMissingAttributeCodesSpec extends ObjectBehavior
{
    function it_is_a_product_completeness()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            30,
            ['name', 'brand', 'description', 'picture']
        );
        $this->shouldHaveType(ProductCompletenessWithMissingAttributeCodes::class);
    }

    function it_throws_an_exception_if_required_count_is_negative()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            -5,
            ['name', 'brand', 'description', 'picture']
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_calculates_the_completeness_ratio()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            30,
            ['name', 'brand', 'description', 'picture']
        );
        $this->ratio()->shouldReturn(86);
    }

    function it_calculates_the_completeness_ratio_when_required_count_is_zero()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            0,
            []
        );
        $this->ratio()->shouldReturn(100);
    }

    function it_returns_floor_integer_33()
    {
        $this->beConstructedWith('ecommerce', 'fr_FR', 3, ['name', 'brand']);
        $this->ratio()->shouldReturn(33);
    }

    function it_returns_floor_integer_66()
    {
        $this->beConstructedWith('ecommerce', 'fr_FR', 3, ['name']);
        $this->ratio()->shouldReturn(66);
    }
}
