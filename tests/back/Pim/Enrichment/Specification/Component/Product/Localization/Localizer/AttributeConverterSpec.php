<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\LocalizerRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class AttributeConverterSpec extends ObjectBehavior
{
    function let(LocalizerRegistryInterface $localizerRegistry, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($localizerRegistry, $attributeRepository);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement(AttributeConverterInterface::class);
    }

    function it_converts_a_number($localizerRegistry, $attributeRepository, LocalizerInterface $localizer)
    {
        $options = ['decimal_separator' => ','];
        $attributeRepository->getAttributeTypeByCodes(['number'])->willReturn(['number' => 'pim_number']);
        $localizerRegistry->getLocalizer('pim_number')->willReturn($localizer);
        $localizer->supports('pim_number')->willReturn(true);
        $localizer->validate('10,45', 'values[number]', $options)->willReturn(null);
        $localizer->delocalize('10,45', $options)->willReturn('10.45');

        $this->convertToDefaultFormats(['number' => [['data' => '10,45']]], $options)
            ->shouldReturn(['number' => [['data' => '10.45']]]);
    }

    function it_does_not_convert_a_product_field(
        $localizerRegistry,
        $attributeRepository,
        LocalizerInterface $localizer
    ) {
        $options = ['decimal_separator' => ','];
        $attributeRepository->getAttributeTypeByCodes(['family'])->willReturn([]);
        $localizerRegistry->getLocalizer('pim_family')->willReturn($localizer);
        $localizer->supports('pim_family')->willReturn(false);

        $this->convertToDefaultFormats(['family' => [['data' => 'boots']]], $options)
            ->shouldReturn(['family' => [['data' => 'boots']]]);
    }

    function it_returns_a_constraint_validation_if_decimal_separator_is_not_expected(
        $localizerRegistry,
        $attributeRepository,
        LocalizerInterface $localizer
    ) {
        $options = ['decimal_separator' => '.'];
        $attributeRepository->getAttributeTypeByCodes(['number'])->willReturn(['number' => 'pim_number']);
        $localizerRegistry->getLocalizer('pim_number')->willReturn($localizer);

        $constraint = new ConstraintViolation('Error with attribute', '', [], '', 'values[number]', '10,45');
        $constraints = new ConstraintViolationList([$constraint]);
        $localizer->supports('pim_number')->willReturn(true);
        $localizer->validate('10,45', 'values[number]', $options)->willReturn($constraints);
        $localizer->delocalize('10,45', $options)->willReturn('10.45');

        $this->convertToDefaultFormats(['number' => [['data' => '10,45']]], $options)
            ->shouldReturn(['number' => [['data' => '10.45']]]);
        $this->getViolations()->shouldHaveCount(1);
    }

    function it_converts_to_localized_format_a_number($localizerRegistry, $attributeRepository, LocalizerInterface $localizer)
    {
        $options = ['decimal_separator' => ','];
        $attributeRepository->getAttributeTypeByCodes(['number'])->willReturn(['number' => 'pim_number']);
        $localizerRegistry->getLocalizer('pim_number')->willReturn($localizer);
        $localizer->supports('pim_number')->willReturn(true);
        $localizer->localize(10.45, $options)->willReturn('10,45');

        $this->convertToLocalizedFormats(['number' => [['data' => 10.45]]], $options)
            ->shouldReturn(['number' => [['data' => '10,45']]]);
    }

    function it_converts_to_localized_format_a_date($localizerRegistry, $attributeRepository, LocalizerInterface $localizer)
    {
        $options = ['date_format' => 'dd-mm-yyyy'];
        $attributeRepository->getAttributeTypeByCodes(['date'])->willReturn(['date' => 'pim_date']);
        $localizerRegistry->getLocalizer('pim_date')->willReturn($localizer);
        $localizer->supports('pim_date')->willReturn(true);
        $localizer->localize('2015/12/31', $options)->willReturn('31-12-2015');

        $this->convertToLocalizedFormats(['date' => [['data' => '2015/12/31']]], $options)
            ->shouldReturn(['date' => [['data' => '31-12-2015']]]);
    }

    function it_does_not_convert_to_localized_format_a_product_field(
        $localizerRegistry,
        $attributeRepository,
        LocalizerInterface $localizer
    ) {
        $options = ['decimal_separator' => ','];
        $attributeRepository->getAttributeTypeByCodes(['family'])->willReturn([]);
        $localizerRegistry->getLocalizer('pim_family')->willReturn($localizer);
        $localizer->supports('pim_family')->willReturn(false);

        $this->convertToLocalizedFormats(['family' => [['data' => 'boots']]], $options)
            ->shouldReturn(['family' => [['data' => 'boots']]]);
    }
}
