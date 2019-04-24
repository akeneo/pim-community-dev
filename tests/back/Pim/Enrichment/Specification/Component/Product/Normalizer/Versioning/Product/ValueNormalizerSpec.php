<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\Localization\Localizer\NumberLocalizer;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ValueNormalizerSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $attributeOptionRepository,
        AttributeInterface $simpleAttribute
    ) {
        $this->beConstructedWith($attributeRepository, $attributeOptionRepository, 4);

        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);

        $simpleAttribute->getCode()->willReturn('simple');
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf(NormalizerInterface::class);
        $this->shouldBeAnInstanceOf(SerializerAwareInterface::class);
    }

    function it_supports_csv_normalization_of_product_value(ValueInterface $value)
    {
        $this->supportsNormalization($value, 'csv')->shouldBe(true);
    }

    function it_supports_flat_normalization_of_product(ValueInterface $value)
    {
        $this->supportsNormalization($value, 'flat')->shouldBe(true);
    }

    function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_a_value_with_null_data(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::TEXT);
        $value->getData()->willReturn(null);
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);
        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('decimal');
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '']);
    }

    function it_normalizes_a_value_with_a_integer_data(
        NumberLocalizer $numberLocalizer,
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::NUMBER);
        $simpleAttribute->isDecimalsAllowed()->willReturn(false);
        $context = ['decimal_separator' => '.'];
        $numberLocalizer->localize('12', $context)->willReturn(12);

        $value->getData()->willReturn(12);
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('decimal');
        $this->normalize($value, 'flat', $context)->shouldReturn(['simple' => '12']);
    }

    function it_normalizes_a_value_with_a_float_data_with_decimals_allowed(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::NUMBER);
        $simpleAttribute->isDecimalsAllowed()->willReturn(true);

        $value->getData()->willReturn('12.2500');
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('decimal');
        $simpleAttribute->isDecimalsAllowed()->willReturn(true);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '12.2500']);
    }

    function it_normalizes_a_value_with_a_float_data_with_decimals_not_allowed(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::NUMBER);
        $simpleAttribute->isDecimalsAllowed()->willReturn(false);

        $value->getData()->willReturn('12.0000');
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('decimal');
        $simpleAttribute->isDecimalsAllowed()->willReturn(false);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '12']);
    }

    function it_normalizes_a_value_with_a_string_data(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::TEXT);

        $value->getData()->willReturn('my data');
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('text');
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => 'my data']);
    }

    function it_normalizes_a_value_with_a_boolean_data(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::BOOLEAN);

        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('boolean');

        $value->getData()->willReturn(false);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '0']);

        $value->getData()->willReturn(true);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '1']);
    }

    function it_normalizes_a_value_with_a_collection_data(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        SerializerInterface $serializer,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);

        $itemOne = new \stdClass();
        $itemTwo = new \stdClass();
        $collection = new ArrayCollection([$itemOne, $itemTwo]);
        $value->getData()->willReturn($collection);
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('prices');

        $serializer->normalize($collection, 'flat', ['field_name' => 'simple'])->shouldBeCalled()->willReturn(['simple' => 'red, blue']);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => 'red, blue']);
    }

    function it_normalizes_a_value_with_an_array_data(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        SerializerInterface $serializer,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);

        $itemOne = new \stdClass();
        $itemTwo = new \stdClass();
        $array = [$itemOne, $itemTwo];
        $value->getData()->willReturn($array);
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('prices');

        $serializer->normalize(Argument::any(), 'flat', ['field_name' => 'simple'])->shouldBeCalled()->willReturn(['simple' => 'red, blue']);
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => 'red, blue']);
    }

    function it_normalizes_a_value_with_ordered_options_with_a_option_collection_data(
        ValueInterface $value,
        AttributeInterface $multiColorAttribute,
        SerializerInterface $serializer,
        AttributeOptionInterface $redOption,
        AttributeOptionInterface $blueOption,
        $attributeRepository,
        $attributeOptionRepository
    ) {
        $multiColorAttribute->getType()->willReturn(AttributeTypes::OPTION_MULTI_SELECT);

        $collection = new ArrayCollection(['red', 'blue']);

        $value->getData()->willReturn($collection);
        $value->getAttributeCode()->willReturn('colors');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);

        $attributeOptionRepository->findOneByIdentifier('colors.red')->willReturn($redOption);
        $attributeOptionRepository->findOneByIdentifier('colors.blue')->willReturn($blueOption);

        $attributeRepository->findOneByIdentifier('colors')->willReturn($multiColorAttribute);
        $multiColorAttribute->getCode()->willReturn('colors');
        $multiColorAttribute->isLocaleSpecific()->willReturn(false);
        $multiColorAttribute->isLocalizable()->willReturn(false);
        $multiColorAttribute->isScopable()->willReturn(false);
        $multiColorAttribute->getBackendType()->willReturn('options');
        $redOption->getSortOrder()->willReturn(10)->shouldBeCalled();
        $blueOption->getSortOrder()->willReturn(11)->shouldBeCalled();

        $serializer->normalize(Argument::type('Doctrine\Common\Collections\ArrayCollection'), 'flat', ['field_name' => 'colors'])
            ->shouldBeCalled()
            ->willReturn(['colors' => 'red, blue']);

        $this->normalize($value, 'flat', [])->shouldReturn(['colors' => 'red, blue']);
    }

    function it_normalizes_a_value_with_a_date_data(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::DATE);

        $value->getData()->willReturn('2000-10-28');
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('date');
        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '2000-10-28']);
    }

    function it_normalizes_a_scopable_product_value(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::TEXT);

        $value->getData()->willReturn('12');
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(true);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $value->getScopeCode()->willReturn('mobile');
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('text');
        $simpleAttribute->isScopable()->willReturn(true);

        $this->normalize($value, 'flat', [])->shouldReturn(['simple-mobile' => '12']);
    }

    function it_normalizes_a_localizable_product_value(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::TEXT);

        $value->getData()->willReturn('12');
        $value->getAttributeCode()->willReturn('simple');
        $value->isLocalizable()->willReturn(true);
        $value->isScopable()->willReturn(false);

        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $value->getLocaleCode()->willReturn('fr_FR');
        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('text');
        $simpleAttribute->isLocalizable()->willReturn(true);

        $this->normalize($value, 'flat', [])->shouldReturn(['simple-fr_FR' => '12']);
    }

    function it_normalizes_a_scopable_and_localizable_product_value(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $simpleAttribute->getType()->willReturn(AttributeTypes::TEXT);

        $value->getData()->willReturn('12');
        $value->getAttributeCode()->willReturn('simple');
        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $value->getLocaleCode()->willReturn('fr_FR');
        $value->getScopeCode()->willReturn('mobile');
        $value->isLocalizable()->willReturn(true);
        $value->isScopable()->willReturn(true);

        $simpleAttribute->isLocaleSpecific()->willReturn(false);
        $simpleAttribute->getBackendType()->willReturn('text');
        $simpleAttribute->isLocalizable()->willReturn(true);
        $simpleAttribute->isScopable()->willReturn(true);

        $this->normalize($value, 'flat', [])->shouldReturn(['simple-fr_FR-mobile' => '12']);
    }

    function it_keeps_non_localizable_and_locale_specific_values(
        ValueInterface $value,
        AttributeInterface $simpleAttribute,
        $attributeRepository
    ) {
        $attributeRepository->findOneByIdentifier('simple')->willReturn($simpleAttribute);
        $simpleAttribute->getType()->willReturn(AttributeTypes::TEXT);

        $value->getData()->willReturn('12');
        $value->getAttributeCode()->willReturn('simple');
        $value->getLocaleCode()->willReturn(null);
        $value->getScopeCode()->willReturn(null);
        $value->isLocalizable()->willReturn(false);
        $value->isScopable()->willReturn(false);
        $simpleAttribute->isLocaleSpecific()->willReturn(true);
        $simpleAttribute->getBackendType()->willReturn('text');
        $simpleAttribute->isLocalizable()->willReturn(false);
        $simpleAttribute->isScopable()->willReturn(false);

        $this->normalize($value, 'flat', [])->shouldReturn(['simple' => '12']);
    }
}
