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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifierMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRequestSpec extends ObjectBehavior
{
    public function let(ProductInterface $product): void
    {
        $this->beConstructedWith($product);
    }

    public function it_is_a_product_subscription_request(): void
    {
        $this->shouldHaveType(ProductSubscriptionRequest::class);
    }

    public function it_does_not_take_missing_values_into_account(
        $product,
        Attribute $manufacturer,
        Attribute $model,
        Attribute $ean,
        ValueInterface $modelValue,
        ValueInterface $eanValue
    ): void {
        $manufacturer->getCode()->willReturn(new AttributeCode('manufacturer'));
        $model->getCode()->willReturn(new AttributeCode('model'));
        $ean->getCode()->willReturn(new AttributeCode('ean'));

        $modelValue->hasData()->willReturn(false);
        $eanValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('123456789123');

        $product->getValue('manufacturer')->willReturn(null);
        $product->getValue('model')->willReturn($modelValue);
        $product->getValue('ean')->willReturn($eanValue);
        $product->getId()->willReturn(42);

        $identifiersMapping = new IdentifiersMapping(
            [
                'upc' => $ean->getWrappedObject(),
                'brand' => $manufacturer->getWrappedObject(),
                'mpn' => $model->getWrappedObject(),
            ]
        );

        $this->getMappedValues($identifiersMapping)->shouldReturn([
            'upc' => '123456789123',
        ]);
    }

    public function it_handles_incomplete_mapping(
        $product,
        IdentifiersMapping $mapping,
        Attribute $ean,
        ValueInterface $eanValue
    ): void {
        $ean->getCode()->willReturn(new AttributeCode('ean'));
        $eanValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('123456789123');

        $product->getValue('ean')->willReturn($eanValue);

        $mapping->getIterator()->willReturn(
            new \ArrayIterator([
                'upc' => new IdentifierMapping('upc', $ean->getWrappedObject()),
                'asin' => null,
                'brand' => null,
                'mpn' => null,
            ])
        );

        $this->getMappedValues($mapping)->shouldReturn(
            [
                'upc' => '123456789123',
            ]
        );
    }

    public function it_handles_mpn_and_brand_as_one_identifier(
        $product,
        Attribute $brand,
        Attribute $mpn,
        ValueInterface $brandValue,
        ValueInterface $mpnValue
    ): void {
        $brand->getCode()->willReturn(new AttributeCode('brand'));
        $brandValue->hasData()->willReturn(true);
        $brandValue->__toString()->willReturn('qwertee');

        $mpn->getCode()->willReturn(new AttributeCode('mpn'));
        $mpnValue->hasData()->willReturn(true);
        $mpnValue->__toString()->willReturn('tshirt-the-witcher');

        $product->getValue('brand')->willReturn($brandValue);
        $product->getValue('mpn')->willReturn($mpnValue);

        $mapping = new IdentifiersMapping(
            [
                'upc' => null,
                'asin' => null,
                'brand' => $brand->getWrappedObject(),
                'mpn' => $mpn->getWrappedObject(),
            ]
        );

        $this->getMappedValues($mapping)->shouldReturn([
            'brand' => 'qwertee',
            'mpn' => 'tshirt-the-witcher',
        ]);
    }

    public function it_does_not_handle_mpn_data_without_brand_data(
        $product,
        IdentifiersMapping $mapping,
        Attribute $brand,
        Attribute $mpn,
        ValueInterface $brandValue,
        ValueInterface $mpnValue
    ): void {
        $brand->getCode()->willReturn(new AttributeCode('brand'));
        $brandValue->hasData()->willReturn(true);
        $brandValue->__toString()->willReturn('qwertee');

        $mpn->getCode()->willReturn(new AttributeCode('mpn'));
        $mpnValue->hasData()->willReturn(false);

        $product->getValue('brand')->willReturn($brandValue);
        $product->getValue('mpn')->willReturn($mpnValue);

        $mapping->getIterator()->willReturn(
            new \ArrayIterator(
                [
                    'upc' => null,
                    'asin' => null,
                    'brand' => $brand->getWrappedObject(),
                    'mpn' => $mpn->getWrappedObject(),
                ]
            )
        );

        $this->getMappedValues($mapping)->shouldReturn([]);
    }

    public function it_does_not_handle_brand_data_without_mpn_data(
        $product,
        IdentifiersMapping $mapping,
        Attribute $brand,
        Attribute $mpn,
        ValueInterface $brandValue,
        ValueInterface $mpnValue
    ): void {
        $brand->getCode()->willReturn(new AttributeCode('brand'));
        $brandValue->hasData()->willReturn(false);

        $mpn->getCode()->willReturn(new AttributeCode('mpn'));
        $mpnValue->hasData()->willReturn(true);
        $mpnValue->__toString()->willReturn('tshirt-the-witcher');

        $product->getValue('brand')->willReturn($brandValue);
        $product->getValue('mpn')->willReturn($mpnValue);

        $mapping->getIterator()->willReturn(
            new \ArrayIterator(
                [
                    'upc' => null,
                    'asin' => null,
                    'brand' => $brand->getWrappedObject(),
                    'mpn' => $mpn->getWrappedObject(),
                ]
            )
        );

        $this->getMappedValues($mapping)->shouldReturn([]);
    }

    public function it_does_not_handle_mpn_value_without_brand_value(
        $product,
        IdentifiersMapping $mapping,
        Attribute $brand,
        Attribute $mpn,
        ValueInterface $brandValue
    ): void {
        $brand->getCode()->willReturn(new AttributeCode('brand'));
        $brandValue->hasData()->willReturn(true);
        $brandValue->__toString()->willReturn('qwertee');

        $mpn->getCode()->willReturn(new AttributeCode('mpn'));

        $product->getValue('brand')->willReturn($brandValue);
        $product->getValue('mpn')->willReturn(null);

        $mapping->getIterator()->willReturn(
            new \ArrayIterator(
                [
                    'upc' => null,
                    'asin' => null,
                    'brand' => $brand->getWrappedObject(),
                    'mpn' => $mpn->getWrappedObject(),
                ]
            )
        );

        $this->getMappedValues($mapping)->shouldReturn([]);
    }

    public function it_does_not_handle_brand_value_without_mpn_value(
        $product,
        IdentifiersMapping $mapping,
        Attribute $brand,
        Attribute $mpn,
        ValueInterface $mpnValue
    ): void {
        $brand->getCode()->willReturn(new AttributeCode('brand'));

        $mpn->getCode()->willReturn(new AttributeCode('mpn'));
        $mpnValue->hasData()->willReturn(true);
        $mpnValue->__toString()->willReturn('tshirt-the-witcher');

        $product->getValue('brand')->willReturn(null);
        $product->getValue('mpn')->willReturn($mpnValue);

        $mapping->getIterator()->willReturn(
            new \ArrayIterator(
                [
                    'upc' => null,
                    'asin' => null,
                    'brand' => $brand->getWrappedObject(),
                    'mpn' => $mpn->getWrappedObject(),
                ]
            )
        );

        $this->getMappedValues($mapping)->shouldReturn([]);
    }
}
