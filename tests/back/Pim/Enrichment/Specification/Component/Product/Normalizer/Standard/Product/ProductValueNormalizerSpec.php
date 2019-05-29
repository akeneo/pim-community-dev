<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($normalizer, $attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValueNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_standard_format_and_product_value(ValueInterface $value)
    {
        $this->supportsNormalization($value, 'standard')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
    }

    function it_normalizes_a_product_value_in_standard_format_with_no_locale_and_no_scope(
        $normalizer,
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $normalizer->normalize('product_value_data', null, ['is_decimals_allowed' => false])
            ->shouldBeCalled()
            ->willReturn('product_value_data');

        $value->getData()->willReturn('product_value_data');
        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);
        $value->getAttributeCode()->willReturn('attribute');

        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getProperties()->willReturn([]);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getMetricFamily()->willReturn(null);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => 'product_value_data',
            ]
        );
    }

    function it_normalizes_a_product_value_in_standard_format_with_locale_and_no_scope(
        $normalizer,
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $normalizer->normalize('product_value_data', null, ['is_decimals_allowed' => false])
            ->shouldBeCalled()
            ->willReturn('product_value_data');

        $value->getData()->willReturn('product_value_data');
        $value->getLocaleCode()->willReturn('en_US');
        $value->getScopeCode()->willReturn(null);
        $value->getAttributeCode()->willReturn('attribute');

        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getProperties()->willReturn([]);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getMetricFamily()->willReturn(null);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => null,
                'data'   => 'product_value_data',
            ]
        );
    }

    function it_normalizes_a_product_value_in_standard_format_with_locale_and_scope(
        $normalizer,
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $normalizer->normalize('product_value_data', null, ['is_decimals_allowed' => false])
            ->shouldBeCalled()
            ->willReturn('product_value_data');

        $value->getData()->willReturn('product_value_data');
        $value->getLocaleCode()->willReturn('en_US');
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getAttributeCode()->willReturn('attribute');

        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getProperties()->willReturn([]);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getMetricFamily()->willReturn(null);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => 'product_value_data',
            ]
        );
    }

    function it_normalizes_a_number_product_value_with_decimal(
        $normalizer,
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $normalizer->normalize('15.50', null, ['is_decimals_allowed' => true])
            ->shouldNotBeCalled();

        $value->getData()->willReturn('15.50');
        $value->getLocaleCode()->willReturn('en_US');
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getAttributeCode()->willReturn('attribute');

        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(true);
        $attribute->getType()->willReturn(AttributeTypes::NUMBER);
        $attribute->getProperties()->willReturn([]);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getMetricFamily()->willReturn(null);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => '15.5000',
            ]
        );
    }

    function it_normalizes_a_number_product_value_without_decimal(
        $normalizer,
        ValueInterface $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $normalizer->normalize('15.00', null, [])
            ->shouldNotBeCalled();

        $value->getData()->willReturn('15.00');
        $value->getLocaleCode()->willReturn('en_US');
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getAttributeCode()->willReturn('attribute');

        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getType()->willReturn(AttributeTypes::NUMBER);
        $attribute->getProperties()->willReturn([]);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getMetricFamily()->willReturn(null);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => 15,
            ]
        );
    }

    function it_normalizes_a_simple_select(
        $normalizer,
        ValueInterface $value,
        AttributeInterface $attribute,
        AttributeOptionInterface $simpleSelect,
        $attributeRepository
    ) {
        $simpleSelect->getCode()->willReturn('optionA');
        $normalizer->normalize($simpleSelect, null, [])->shouldNotBeCalled();

        $value->getData()->willReturn('optionA');
        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);
        $value->getAttributeCode()->willReturn('attribute');

        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::OPTION_SIMPLE_SELECT);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getProperties()->willReturn([]);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getMetricFamily()->willReturn(null);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => 'optionA',
            ]
        );
    }

    function it_normalizes_a_multi_select(
        $normalizer,
        OptionsValueInterface $value,
        AttributeInterface $attribute,
        AttributeOptionInterface $multiSelect,
        $attributeRepository
    ) {
        $multiSelect->getCode()->willReturn('optionA');
        $normalizer->normalize($multiSelect, null, [])->shouldNotBeCalled();

        $value->getData()->willReturn(['optionA']);
        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);
        $value->getAttributeCode()->willReturn('attribute');

        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getProperties()->willReturn([]);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getMetricFamily()->willReturn(null);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => ['optionA'],
            ]
        );
    }

    function it_normalizes_a_scalar(
        ScalarValue $value,
        AttributeInterface $attribute,
        $attributeRepository
    ) {
        $value->getData()->willReturn('foo');
        $value->getLocaleCode()->willReturn('en_US');
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getAttributeCode()->willReturn('attribute');

        $attributeRepository->findOneByIdentifier('attribute')->willReturn($attribute);
        $attribute->getType()->willReturn(AttributeTypes::TEXT);
        $attribute->isDecimalsAllowed()->willReturn(false);
        $attribute->getProperties()->willReturn([]);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->getMetricFamily()->willReturn(null);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => 'foo',
            ]
        );
    }

    function it_does_not_call_the_attribute_repository_if_the_data_is_in_the_context(ScalarValue $value)
    {
        $value->getData()->willReturn('foo');
        $value->getLocaleCode()->willReturn('en_US');
        $value->getScopeCode()->willReturn('ecommerce');
        $value->getAttributeCode()->willReturn('attribute');

        $this->normalize($value, null, ['attributes' => ['attribute' => new Attribute('attribute', AttributeTypes::TEXT, [], true, true, null, false)]])->shouldReturn(
            [
                'locale' => 'en_US',
                'scope'  => 'ecommerce',
                'data'   => 'foo',
            ]
        );
    }
}
