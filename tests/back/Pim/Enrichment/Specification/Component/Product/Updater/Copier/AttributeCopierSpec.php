<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Copier;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\AttributeCopier;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeValidatorHelper;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeCopierSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesBuilderInterface $builder,
        AttributeValidatorHelper $attrValidatorHelper,
        NormalizerInterface $normalizer
    ) {
        $this->beConstructedWith(
            $builder,
            $attrValidatorHelper,
            $normalizer,
            ['foo', 'bar'],
            ['foo', 'bar']
        );
    }

    function it_is_a_copier()
    {
        $this->shouldImplement(CopierInterface::class);
    }

    function it_supports_attributes(
        AttributeInterface $fromFooAttribute,
        AttributeInterface $toFooAttribute,
        AttributeInterface $fromTextareaAttribute,
        AttributeInterface $fromImageAttribute,
        AttributeInterface $toImageAttribute,
        AttributeInterface $fromFileAttribute,
        AttributeInterface $toFileAttribute,
        AttributeInterface $toTextareaAttribute
    ) {
        $fromFooAttribute->getType()->willReturn('foo');
        $toFooAttribute->getType()->willReturn('foo');
        $this->supportsAttributes($fromFooAttribute, $toFooAttribute)->shouldReturn(true);

        $fromFooAttribute->getType()->willReturn('foo');
        $toFooAttribute->getType()->willReturn('bar');
        $this->supportsAttributes($fromFooAttribute, $toFooAttribute)->shouldReturn(false);

        $fromTextareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $toTextareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromTextareaAttribute, $toTextareaAttribute)->shouldReturn(false);

        $fromImageAttribute->getType()->willReturn('pim_catalog_image');
        $toImageAttribute->getType()->willReturn('pim_catalog_image');
        $this->supportsAttributes($fromImageAttribute, $toImageAttribute)->shouldReturn(false);

        $fromFileAttribute->getType()->willReturn('pim_catalog_file');
        $toFileAttribute->getType()->willReturn('pim_catalog_file');
        $this->supportsAttributes($fromImageAttribute, $toImageAttribute)->shouldReturn(false);

        $fromFooAttribute->getType()->willReturn('foo');
        $toTextareaAttribute->getType()->willReturn('pim_catalog_textarea');
        $this->supportsAttributes($fromFooAttribute, $toTextareaAttribute)->shouldReturn(false);
    }

    function it_copies_a_boolean_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $normalizer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ScalarValue $fromValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($fromValue, 'standard')->shouldBeCalled()
            ->willReturn([
                'locale' => 'fr_FR',
                'scope' => 'mobile',
                'data' => true
            ]);

        $normalizer->normalize(null, 'standard')->shouldBeCalled()->willReturn(null);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromValue);
        $builder
            ->addOrReplaceValue($product1, $toAttribute, $toLocale, $toScope, true)
            ->shouldBeCalled();

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder
            ->addOrReplaceValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }

    function it_copies_a_date_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $normalizer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ScalarValue $fromValue,
        ScalarValue $toValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($fromValue, 'standard')->shouldBeCalled()->willReturn([
            'locale' => 'fr_FR',
            'scope' => 'mobile',
            'data' => '1970-01-01'
        ]);

        $normalizer->normalize(null, 'standard')->shouldBeCalled()->willReturn(null);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromValue);
        $builder
            ->addOrReplaceValue($product1, $toAttribute, $toLocale, $toScope, '1970-01-01')
            ->shouldBeCalled()
            ->willReturn($toValue);

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder
            ->addOrReplaceValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }

    function it_copies_number_value_to_a_product_value(
        $builder,
        $attrValidatorHelper,
        $normalizer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ScalarValue $fromValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($fromValue, 'standard')->shouldBeCalled()->willReturn([
            'locale' => 'fr_FR',
            'scope' => 'mobile',
            'data' => 123
        ]);

        $normalizer->normalize(null, 'standard')->shouldBeCalled()->willReturn(null);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromValue);
        $builder
            ->addOrReplaceValue($product1, $toAttribute, $toLocale, $toScope, 123)
            ->shouldBeCalled();

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder
            ->addOrReplaceValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }

    function it_copies_text_value_to_a_product_value(
        $attrValidatorHelper,
        $builder,
        $normalizer,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product1,
        ProductInterface $product2,
        ScalarValue $fromValue
    ) {
        $fromLocale = 'fr_FR';
        $toLocale = 'fr_FR';
        $toScope = 'mobile';
        $fromScope = 'mobile';

        $fromAttribute->getCode()->willReturn('fromAttributeCode');
        $toAttribute->getCode()->willReturn('toAttributeCode');

        $attrValidatorHelper->validateLocale(Argument::cetera())->shouldBeCalled();
        $attrValidatorHelper->validateScope(Argument::cetera())->shouldBeCalled();

        $normalizer->normalize($fromValue, 'standard')->shouldBeCalled()->willReturn([
            'locale' => 'fr_FR',
            'scope' => 'mobile',
            'data' => 'data'
        ]);

        $normalizer->normalize(null, 'standard')->shouldBeCalled()->willReturn(null);

        $product1->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn($fromValue);
        $builder
            ->addOrReplaceValue($product1, $toAttribute, $toLocale, $toScope, 'data')
            ->shouldBeCalled();

        $product2->getValue('fromAttributeCode', $fromLocale, $fromScope)->willReturn(null);
        $builder
            ->addOrReplaceValue($product2, $toAttribute, $toLocale, $toScope, null)
            ->shouldBeCalled();

        $products = [$product1, $product2];
        foreach ($products as $product) {
            $this->copyAttributeData(
                $product,
                $product,
                $fromAttribute,
                $toAttribute,
                [
                    'from_locale' => $fromLocale,
                    'to_locale' => $toLocale,
                    'from_scope' => $fromScope,
                    'to_scope' => $toScope
                ]
            );
        }
    }

    function it_throws_an_exception_when_locale_is_expected(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects a locale, none given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(true);
        $attrValidatorHelper->validateLocale($fromAttribute, null)->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                AttributeCopier::class,
                $e
            )
        )->during('copyAttributeData', [$product, $product, $fromAttribute, $toAttribute, []]);
    }

    function it_throws_an_exception_when_locale_is_not_expected(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" does not expect a locale, "en_US" given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(false);
        $attrValidatorHelper->validateLocale($fromAttribute, 'en_US')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                AttributeCopier::class,
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => 'en_US', 'from_scope' => 'ecommerce']]
        );
    }

    function it_throws_an_exception_when_locale_is_expected_but_not_activated(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects an existing and activated locale, "uz-UZ" given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(true);
        $attrValidatorHelper->validateLocale($fromAttribute, 'uz-UZ')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                AttributeCopier::class,
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => 'uz-UZ', 'from_scope' => 'ecommerce']]
        );
    }

    function it_throws_an_exception_when_scope_is_expected(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects a scope, none given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(false);
        $fromAttribute->isScopable()->willReturn(true);
        $attrValidatorHelper->validateLocale($fromAttribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($fromAttribute, null)->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                AttributeCopier::class,
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => null, 'from_scope' => null]]
        );
    }

    function it_throws_an_exception_when_scope_is_not_expected(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" does not expect a scope, "ecommerce" given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(false);
        $fromAttribute->isScopable()->willReturn(false);
        $attrValidatorHelper->validateLocale($fromAttribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($fromAttribute, 'ecommerce')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                AttributeCopier::class,
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => null, 'from_scope' => 'ecommerce']]
        );
    }

    function it_throws_an_exception_when_scope_is_expected_but_not_existing(
        $attrValidatorHelper,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        ProductInterface $product
    ) {
        $e = new \LogicException('Attribute "attributeCode" expects an existing scope, "ecommerce" given.');
        $fromAttribute->getCode()->willReturn('attributeCode');
        $fromAttribute->isLocalizable()->willReturn(false);
        $fromAttribute->isScopable()->willReturn(true);
        $attrValidatorHelper->validateLocale($fromAttribute, null)->shouldBeCalled();
        $attrValidatorHelper->validateScope($fromAttribute, 'ecommerce')->willThrow($e);
        $this->shouldThrow(
            InvalidPropertyException::expectedFromPreviousException(
                'attributeCode',
                AttributeCopier::class,
                $e
            )
        )->during(
            'copyAttributeData',
            [$product, $product, $fromAttribute, $toAttribute, ['from_locale' => null, 'from_scope' => 'ecommerce']]
        );
    }
}
