<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read;

use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductIdentifierValues;
use PhpSpec\ObjectBehavior;

class ProductIdentifierValuesSpec extends ObjectBehavior
{
    public function it_is_a_product_identifier_values_read_model(): void
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType(ProductIdentifierValues::class);
    }

    public function it_exposes_identifier_values(): void
    {
        $values = [
            'asin' => 'ABC123456',
            'upc' => '123456789123',
            'brand' => 'akeneo',
            'mpn' => 'pim-123',
        ];
        $this->beConstructedWith($values);

        $this->identifierValues()->shouldBeLike($values);
    }

    public function it_completes_missing_identifiers_with_null(): void
    {
        $this->beConstructedWith(['upc' => '987654321987']);
        $this->identifierValues()->shouldBeLike(
            [
                'asin' => null,
                'upc' => '987654321987',
                'brand' => null,
                'mpn' => null,
            ]
        );
    }
}
