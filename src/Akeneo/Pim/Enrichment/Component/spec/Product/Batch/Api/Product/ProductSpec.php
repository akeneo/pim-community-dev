<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product;

use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Product;
use Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Value\ProductValue;
use PhpSpec\ObjectBehavior;

class ProductSpec extends ObjectBehavior
{
    function it_is_a_product()
    {
        $this->shouldHaveType(Product::class);
    }
    
    function it_constructs_a_product_dto_from_api_format()
    {
        $this->beConstructedThrough('fromApiFormat', [[
            'identifier' => 'identifier_product',
            'enabled' => true,
            'groups' => ['groupA', 'groupB'],
            'family' => 'familyA',
            'categories' => ['master', 'categoryA'],
            'values' => [
                'a_file' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'file'
                    ],
                ],
                'a_date' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => '2016-06-13T00:00:00+02:00'
                    ]
                ]
            ]
        ]]);

        $this->identifier()->shouldReturn('identifier_product');
        $this->enabled()->shouldReturn(true);
        $this->groups()->shouldReturn(['groupA', 'groupB']);
        $this->family()->shouldReturn('familyA');
        $this->categories()->shouldReturn(['master', 'categoryA']);
        $this->values()->valuesIndexedByAttribute()->shouldBeLike([
            'a_file' => [new ProductValue('a_file', null, null, 'file')],
            'a_date' => [new ProductValue('a_date', 'en_US', 'ecommerce', '2016-06-13T00:00:00+02:00')],
        ]);
    }

}
