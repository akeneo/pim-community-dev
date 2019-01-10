<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use PhpSpec\ObjectBehavior;

class ProductIdentifierValuesSpec extends ObjectBehavior
{
    public function it_is_a_product_identifier_values_read_model(): void
    {
        $this->beConstructedWith(42, []);
        $this->shouldHaveType(ProductIdentifierValues::class);
    }

    public function it_exposes_product_id(): void
    {
        $this->beConstructedWith(42, []);
        $this->productId()->shouldReturn(42);
    }

    public function it_exposes_identifier_values(): void
    {
        $values = [
            'asin' => 'ABC123456',
            'upc' => '123456789123',
            'brand' => 'akeneo',
            'mpn' => 'pim-123',
        ];
        $this->beConstructedWith(42, $values);

        $this->getValue('asin')->shouldReturn('ABC123456');
        $this->getValue('upc')->shouldReturn('123456789123');
        $this->getValue('mpn')->shouldReturn('pim-123');
        $this->getValue('brand')->shouldReturn('akeneo');
    }

    public function it_returns_null_if_asked_identifier_does_not_exist(): void
    {
        $this->beConstructedWith(42, ['upc' => '987654321987']);
        $this->getValue('non_existing_franklin_identifier')->shouldReturn(null);
    }
}
