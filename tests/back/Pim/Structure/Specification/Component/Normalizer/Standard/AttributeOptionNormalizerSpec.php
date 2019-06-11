<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Standard\AttributeOptionNormalizer;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;

class AttributeOptionNormalizerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeOptionNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(AttributeOptionInterface $attributeOption)
    {
        $this->supportsNormalization($attributeOption, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($attributeOption, 'xml')->shouldReturn(false);
        $this->supportsNormalization($attributeOption, 'json')->shouldReturn(false);
    }

    function it_normalizes_an_attribute_option_without_label(
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute
    ) {
        $attributeOption->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
        $attributeOption->getCode()->willReturn('red');
        $attributeOption->getOptionValues()->willReturn([]);
        $attributeOption->getSortOrder()->willReturn(1);

        $this->normalize($attributeOption, 'standard')->shouldReturn([
            'code' => 'red',
            'attribute' => 'color',
            'sort_order' => 1,
            'labels' => []
        ]);
    }

    function it_normalizes_an_attribute_option(
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute,
        AttributeOptionValueInterface $valueEn,
        AttributeOptionValueInterface $valueFr,
        LocaleInterface $enLocale,
        LocaleInterface $frLocale,
        $localeRepository
    ) {
        $attributeOption->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
        $attributeOption->getCode()->willReturn('red');
        $attributeOption->getOptionValues()->willReturn([
            'en_US' => $valueEn,
            'fr_FR' => $valueFr,
        ]);
        $attributeOption->getSortOrder()->willReturn(1);
        $valueEn->getLocale()->willReturn('en_US');
        $valueEn->getValue()->willReturn('Red');
        $valueFr->getLocale()->willReturn('fr_FR');
        $valueFr->getValue()->willReturn('Rouge');

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frLocale);
        $frLocale->isActivated()->willReturn(true);

        $this->normalize($attributeOption, 'standard', ['locales' => ['en_US', 'fr_FR', 'de_DE']])->shouldReturn([
            'code' => 'red',
            'attribute' => 'color',
            'sort_order' => 1,
            'labels' => [
                'en_US' => 'Red',
                'fr_FR' => 'Rouge',
                'de_DE' => null
            ]
        ]);
    }

    function it_normalizes_an_attribute_option_without_exposing_disabled_locales(
        AttributeOptionInterface $attributeOption,
        AttributeInterface $attribute,
        AttributeOptionValueInterface $valueEn,
        AttributeOptionValueInterface $valueFr,
        LocaleInterface $enLocale,
        LocaleInterface $frLocale,
        $localeRepository
    ) {
        $attributeOption->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');
        $attributeOption->getCode()->willReturn('red');
        $attributeOption->getOptionValues()->willReturn([
            'en_US' => $valueEn,
            'fr_FR' => $valueFr,
        ]);
        $attributeOption->getSortOrder()->willReturn(1);
        $valueEn->getLocale()->willReturn('en_US');
        $valueEn->getValue()->willReturn('Red');
        $valueFr->getLocale()->willReturn('fr_FR');
        $valueFr->getValue()->willReturn('Rouge');

        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frLocale);
        $frLocale->isActivated()->willReturn(false);

        $this->normalize($attributeOption, 'standard', ['locales' => ['en_US', 'fr_FR', 'de_DE']])->shouldReturn([
            'code' => 'red',
            'attribute' => 'color',
            'sort_order' => 1,
            'labels' => [
                'en_US' => 'Red',
                'fr_FR' => null,
                'de_DE' => null,
            ]
        ]);
    }
}
