<?php

namespace spec\Pim\Component\Catalog\ProductAndProductModel\Query;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\ProductAndProductModel\Query\CompleteFilterData;

class CompleteFilterDataSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            ['channel_code' => 'ecommerce', 'locale_code' => 'en_US', 'complete' => "0", 'incomplete' => "1"],
            ['channel_code' => 'print', 'locale_code' => 'en_US', 'complete' => "0", 'incomplete' => "1"],
            ['channel_code' => 'print', 'locale_code' => 'fr_FR', 'complete' => "1", 'incomplete' => "0"],
            ['channel_code' => 'tablet', 'locale_code' => 'en_US', 'complete' => "0", 'incomplete' => "1"],
            ['channel_code' => 'tablet', 'locale_code' => 'fr_FR', 'complete' => "0", 'incomplete' => "0"],
            ['channel_code' => 'tablet', 'locale_code' => 'de_DE', 'complete' => "1", 'incomplete' => "1"],
        ]);
    }

    function it_throws_an_exception_if_the_locale_is_invalid()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            '__construct',
            [[['channel_code' => 'ecommerce', 'complete' => "0", 'incomplete' => "0"]]]
        );
    }
    function it_throws_an_exception_if_the_channel_is_invalid()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            '__construct',
            [[['locale_code' => 'en_US', 'complete' => "0", 'incomplete' => "0"]]]
        );
    }
    function it_throws_an_exception_if_complete_is_invalid()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            '__construct',
            [[['channel_code' => 'ecommerce', 'locale_code' => 'en_US', 'complete' => "0", 'incomplete' => "11"]]]
        );
    }
    function it_throws_an_exception_if_incomplete_is_invalid()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            '__construct',
            [[['channel_code' => 'ecommerce', 'locale_code' => 'en_US', 'complete' => "0", 'incomplete' => "11"]]]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompleteFilterData::class);
    }

    function it_has_complete_variant_product()
    {
        $this->allIncomplete()->shouldReturn([
            'ecommerce' => [
                'en_US' => 1,
            ],
            'print' => [
                'en_US' => 1,
                'fr_FR' => 0,
            ],
            'tablet' => [
                'en_US' => 1,
                'fr_FR' => 1,
                'de_DE' => 0,
            ],
        ]);
    }

    function it_has_incomplete_variant_product()
    {
        $this->allComplete()->shouldReturn([
            'ecommerce' => [
                'en_US' => 0,
            ],
            'print' => [
                'en_US' => 0,
                'fr_FR' => 1,
            ],
            'tablet' => [
                'en_US' => 0,
                'fr_FR' => 1,
                'de_DE' => 0,
            ],
        ]);
    }
}
