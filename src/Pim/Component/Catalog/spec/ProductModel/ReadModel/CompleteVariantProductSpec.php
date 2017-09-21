<?php

namespace spec\Pim\Component\Catalog\ProductModel\ReadModel;

use Pim\Component\Catalog\ProductModel\ReadModel\CompleteVariantProduct;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompleteVariantProductSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            [
                ['ch' => 'ecommerce', 'lo' => 'en_US',  'co' => 0, 'pr' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'ecommerce', 'lo' => 'fr_FR', 'co' => 1, 'pr' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'print', 'lo' => 'en_US', 'co' => 1, 'pr' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'print', 'lo' => 'fr_FR', 'co' => 1, 'pr' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'mobile', 'lo' => 'en_US', 'co' => 0, 'pr' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'mobile', 'lo' => 'fr_FR', 'co' => 1, 'pr' => 'biker-jacket-polyester-xxs'],
                ['ch' => 'ecommerce', 'lo' => 'en_US', 'co' => 1, 'pr' => 'biker-jacket-polyester-m'],
                ['ch' => 'ecommerce', 'lo' => 'fr_FR', 'co' => 1, 'pr' => 'biker-jacket-polyester-m'],
                ['ch' => 'print', 'lo' => 'en_US', 'co' => 0, 'pr' => 'biker-jacket-polyester-m'],
                ['ch' => 'print', 'lo' => 'fr_FR', 'co' => 0, 'pr' => 'biker-jacket-polyester-m'],
                ['ch' => 'mobile', 'lo' => 'en_US', 'co' => 1, 'pr' => 'biker-jacket-polyester-m'],
                ['ch' => 'mobile', 'lo' => 'fr_FR', 'co' => 1, 'biker-jacket-polyester-m'],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompleteVariantProduct::class);
    }

    function it_calculates_completenesses()
    {
        $this->values()->shouldReturn([
            'completenesses' => [
                'ecommerce' => [
                    'en_US' => 1,
                    'fr_FR' => 2,
                ],
                'print' => [
                    'en_US' => 1,
                    'fr_FR' => 1,
                ],
                'mobile' => [
                    'en_US' => 1,
                    'fr_FR' => 2,
                ],
            ],
            'total' => 2
        ]);
    }

    function it_has_ratio()
    {
        $this->value('mobile', 'fr_FR')->shouldReturn([
            'complete' => 2,
            'total' => 2
        ]);
    }

    function it_throws_an_exception_if_the_completeness_does_not_exist()
    {
        $this->value('tablet', 'fr_FR')->shouldReturn([
            'complete' => 0,
            'total' => 2
        ]);
    }
}
