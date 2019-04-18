<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Product;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRequestSpec extends ObjectBehavior
{
    public function it_is_a_product_subscription_request(): void
    {
        $productId = new ProductId(42);
        $this->beConstructedWith($productId, $this->buildFamily(), new ProductIdentifierValues($productId, []), 'a_product');
        $this->shouldHaveType(ProductSubscriptionRequest::class);
    }

    public function it_returns_the_product_id()
    {
        $productId = new ProductId(42);
        $this->beConstructedWith($productId, $this->buildFamily(), new ProductIdentifierValues($productId, []), 'a_product');

        $this->getProductId()->shouldReturn($productId);
    }

    public function it_returns_the_product_identifier()
    {
        $productId = new ProductId(42);
        $this->beConstructedWith($productId, $this->buildFamily(), new ProductIdentifierValues($productId, []), 'a_product');

        $this->getProductIdentifier()->shouldReturn('a_product');
    }

    public function it_returns_the_family()
    {
        $productId = new ProductId(42);
        $family = $this->buildFamily();
        $this->beConstructedWith($productId, $family, new ProductIdentifierValues($productId, []), 'a_product');

        $this->getFamily()->shouldReturn($family);
    }

    public function it_returns_the_mapped_values()
    {
        $productId = new ProductId(42);
        $productIdentifierValues = new ProductIdentifierValues($productId, [
            'upc' => '123456789123',
            'asin' => '987654',
        ]);

        $this->beConstructedWith($productId, $this->buildFamily(), $productIdentifierValues, 'a_product');

        $this->getMappedValues()->shouldReturn([
            'upc' => '123456789123',
            'asin' => '987654',
        ]);
    }

    public function it_does_not_take_missing_values_into_account(): void
    {
        $productId = new ProductId(42);
        $productIdentifierValues = new ProductIdentifierValues($productId, [
            'upc' => '123456789123',
            'asin' => '987654',
            'brand' => null,
            'mpn' => null,
        ]);

        $this->beConstructedWith($productId, $this->buildFamily(), $productIdentifierValues, 'a_product');

        $this->getMappedValues()->shouldReturn([
            'upc' => '123456789123',
            'asin' => '987654',
        ]);
    }

    public function it_handles_mpn_and_brand_as_one_identifier(): void
    {
        $productId = new ProductId(42);
        $productIdentifierValues = new ProductIdentifierValues($productId, [
            'upc' => null,
            'asin' => null,
            'brand' => 'qwertee',
            'mpn' => 'tshirt-the-witcher',
        ]);

        $this->beConstructedWith($productId, $this->buildFamily(), $productIdentifierValues, 'a_product');

        $this->getMappedValues()->shouldReturn([
            'brand' => 'qwertee',
            'mpn' => 'tshirt-the-witcher',
        ]);
    }

    public function it_does_not_handle_brand_value_without_mpn_value(): void
    {
        $productId = new ProductId(42);
        $productIdentifierValues = new ProductIdentifierValues($productId, [
            'upc' => '123456789123',
            'asin' => null,
            'brand' => 'qwertee',
            'mpn' => null,
        ]);

        $this->beConstructedWith($productId, $this->buildFamily(), $productIdentifierValues, 'a_product');

        $this->getMappedValues()->shouldReturn(['upc' => '123456789123']);
    }

    public function it_does_not_handle_mpn_value_without_brand_value(): void
    {
        $productId = new ProductId(42);
        $productIdentifierValues = new ProductIdentifierValues($productId, [
            'upc' => '123456789123',
            'asin' => null,
            'brand' => null,
            'mpn' => 'tshirt-the-witcher',
        ]);

        $this->beConstructedWith($productId, $this->buildFamily(), $productIdentifierValues, 'a_product');

        $this->getMappedValues()->shouldReturn(['upc' => '123456789123']);
    }

    private function buildFamily(): Family
    {
        return new Family(
            new FamilyCode('a_family'),
            ['en_US' => 'a family']
        );
    }
}
