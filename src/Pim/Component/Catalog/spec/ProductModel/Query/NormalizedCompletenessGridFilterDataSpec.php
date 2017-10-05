<?php

namespace spec\Pim\Component\Catalog\ProductModel\Query;

use Pim\Component\Catalog\ProductModel\Query\NormalizedCompletenessGridFilterData;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NormalizedCompletenessGridFilterDataSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            ['channel_code' => 'ecommerce', 'locale_code' => 'en_US',  'complete' => "0", 'incomplete' => "1"],
            ['channel_code' => 'print', 'locale_code' => 'en_US', 'complete' => "0", 'incomplete' => "1"],
            ['channel_code' => 'print', 'locale_code' => 'fr_FR', 'complete' => "1", 'incomplete' => "0"],
            ['channel_code' => 'tablet', 'locale_code' => 'en_US', 'complete' => "0", 'incomplete' => "1"],
            ['channel_code' => 'tablet', 'locale_code' => 'fr_FR', 'complete' => "0", 'incomplete' => "0"],
            ['channel_code' => 'tablet', 'locale_code' => 'de_DE', 'complete' => "1", 'incomplete' => "1"],
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NormalizedCompletenessGridFilterData::class);
    }

    function it_has_complete_variant_product()
    {
        $this->atLeastComplete()->shouldReturn([
            'ecommerce' => [
                'en_US' => 0,
            ],
            'print' => [
                'en_US' => 0,
                'fr_FR' => 1,
            ],
            'tablet' => [
                'en_US' => 0,
                'fr_FR' => 0,
                'de_DE' => 1,
            ],
        ]);
    }

    function it_has_incomplete_variant_product()
    {
        $this->atLeastIncomplete()->shouldReturn([
            'ecommerce' => [
                'en_US' => 1,
            ],
            'print' => [
                'en_US' => 1,
                'fr_FR' => 0,
            ],
            'tablet' => [
                'en_US' => 1,
                'fr_FR' => 0,
                'de_DE' => 1,
            ],
        ]);
    }
}
