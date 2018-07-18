<?php

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Model;

use Akeneo\Pim\Automation\SuggestData\Component\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Component\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Component\Product\ProductCodeCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class ProductSubscriptionRequestSpec extends ObjectBehavior
{
    function let(ProductInterface $product)
    {
        $this->beConstructedWith($product);
    }

    function it_is_a_product_subscription_request()
    {
        $this->shouldHaveType(ProductSubscriptionRequest::class);
    }

    function it_throws_an_exception_if_mapped_attribute_is_scopable(
        IdentifiersMapping $mapping,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->getCode()->willReturn('foo');
        $mapping->getIterator()->willReturn(new \ArrayIterator(['asin' => $attribute->getWrappedObject()]));

        $this->shouldThrow(
            new \LogicException(
                sprintf(
                    'Mapped attribute "%s" for code "%s" should not be localizable, scopable nor locale specific',
                    'foo',
                    'asin'
                )
            )
        )->during('getMappedValues', [$mapping]);
    }

    function it_throws_an_exception_if_mapped_attribute_is_localizable(
        IdentifiersMapping $mapping,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->getCode()->willReturn('foo');
        $mapping->getIterator()->willReturn(new \ArrayIterator(['asin' => $attribute->getWrappedObject()]));

        $this->shouldThrow(
            new \LogicException(
                sprintf(
                    'Mapped attribute "%s" for code "%s" should not be localizable, scopable nor locale specific',
                    'foo',
                    'asin'
                )
            )
        )->during('getMappedValues', [$mapping]);
    }

    function it_throws_an_exception_if_mapped_attribute_is_locale_specific(
        IdentifiersMapping $mapping,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(true);
        $attribute->getCode()->willReturn('foo');
        $mapping->getIterator()->willReturn(new \ArrayIterator(['asin' => $attribute->getWrappedObject()]));

        $this->shouldThrow(
            new \LogicException(
                sprintf(
                    'Mapped attribute "%s" for code "%s" should not be localizable, scopable nor locale specific',
                    'foo',
                    'asin'
                )
            )
        )->during('getMappedValues', [$mapping]);
    }

    function it_does_not_take_missing_values_into_account(
        $product,
        IdentifiersMapping $mapping,
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        ValueInterface $modelValue,
        ValueInterface $eanValue
    ) {
        $manufacturer->getCode()->willReturn('manufacturer');
        $manufacturer->isScopable()->willReturn(false);
        $manufacturer->isLocalizable()->willReturn(false);
        $manufacturer->isLocaleSpecific()->willReturn(false);

        $model->getCode()->willReturn('model');
        $model->isScopable()->willReturn(false);
        $model->isLocalizable()->willReturn(false);
        $model->isLocaleSpecific()->willReturn(false);

        $ean->getCode()->willReturn('ean');
        $ean->isScopable()->willReturn(false);
        $ean->isLocalizable()->willReturn(false);
        $ean->isLocaleSpecific()->willReturn(false);

        $modelValue->hasData()->willReturn(false);
        $eanValue->hasData()->willReturn(true);
        $eanValue->__toString()->willReturn('123456789123');

        $product->getValue('manufacturer')->willReturn(null);
        $product->getValue('model')->willReturn($modelValue);
        $product->getValue('ean')->willReturn($eanValue);
        $product->getId()->willReturn(42);

        $mapping->getIterator()->willReturn(
            new \ArrayIterator(
                [
                    'upc'   => $ean->getWrappedObject(),
                    'brand' => $manufacturer->getWrappedObject(),
                    'mpn'   => $model->getWrappedObject(),
                ]
            )
        );

        $this->getMappedValues($mapping)->shouldReturn(
            [
                'upc' => '123456789123',
            ]
        );
    }
}
