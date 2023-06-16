<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query;

use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompleteVariantProductsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            [
                ['channel_code' => 'ecommerce', 'locale_code' => 'en_US',  'complete' => 0, 'product_uuid' => 'ace0d2a2-588c-4aa9-aa7d-9a2b05ce3c45'],
                ['channel_code' => 'ecommerce', 'locale_code' => 'fr_FR', 'complete' => 1, 'product_uuid' => 'ace0d2a2-588c-4aa9-aa7d-9a2b05ce3c45'],
                ['channel_code' => 'print', 'locale_code' => 'en_US', 'complete' => 1, 'product_uuid' => 'ace0d2a2-588c-4aa9-aa7d-9a2b05ce3c45'],
                ['channel_code' => 'print', 'locale_code' => 'fr_FR', 'complete' => 1, 'product_uuid' => 'ace0d2a2-588c-4aa9-aa7d-9a2b05ce3c45'],
                ['channel_code' => 'mobile', 'locale_code' => 'en_US', 'complete' => 0, 'product_uuid' => 'ace0d2a2-588c-4aa9-aa7d-9a2b05ce3c45'],
                ['channel_code' => 'mobile', 'locale_code' => 'fr_FR', 'complete' => 1, 'product_uuid' => 'ace0d2a2-588c-4aa9-aa7d-9a2b05ce3c45'],
                ['channel_code' => 'ecommerce', 'locale_code' => 'en_US', 'complete' => 1, 'product_uuid' => 'e5d46e64-e50e-4c8d-92c2-f12e2cbf92c9'],
                ['channel_code' => 'ecommerce', 'locale_code' => 'fr_FR', 'complete' => 1, 'product_uuid' => 'e5d46e64-e50e-4c8d-92c2-f12e2cbf92c9'],
                ['channel_code' => 'print', 'locale_code' => 'en_US', 'complete' => 0, 'product_uuid' => 'e5d46e64-e50e-4c8d-92c2-f12e2cbf92c9'],
                ['channel_code' => 'print', 'locale_code' => 'fr_FR', 'complete' => 0, 'product_uuid' => 'e5d46e64-e50e-4c8d-92c2-f12e2cbf92c9'],
                ['channel_code' => 'mobile', 'locale_code' => 'en_US', 'complete' => 1, 'product_uuid' => 'e5d46e64-e50e-4c8d-92c2-f12e2cbf92c9'],
                ['channel_code' => 'mobile', 'locale_code' => 'fr_FR', 'complete' => 1, 'product_uuid' => 'e5d46e64-e50e-4c8d-92c2-f12e2cbf92c9'],
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompleteVariantProducts::class);
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
