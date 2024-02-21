<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use PhpSpec\ObjectBehavior;

class ProductCompletenessSpec extends ObjectBehavior
{
    function it_is_a_product_completeness()
    {
        $this->beConstructedWith('ecommerce', 'fr_FR', 30, 4);
        $this->shouldHaveType(ProductCompleteness::class);
    }

    function it_throws_an_exception_if_required_count_is_negative()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            -5,
            4
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_if_missing_count_is_negative()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            5,
            -4
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_if_missing_count_is_greater_than_required_count()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            5,
            10
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_calculates_the_completeness_ratio()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            30,
            4
        );
        $this->ratio()->shouldReturn(86);
    }

    function it_calculates_the_completeness_ratio_when_required_count_is_zero()
    {
        $this->beConstructedWith(
            'ecommerce',
            'fr_FR',
            0,
            0
        );
        $this->ratio()->shouldReturn(100);
    }

    function it_returns_floor_integer_33()
    {
        $this->beConstructedWith('ecommerce', 'fr_FR', 3, 2);
        $this->ratio()->shouldReturn(33);
    }

    function it_returns_floor_integer_66()
    {
        $this->beConstructedWith('ecommerce', 'fr_FR', 3, 1);
        $this->ratio()->shouldReturn(66);
    }
}
