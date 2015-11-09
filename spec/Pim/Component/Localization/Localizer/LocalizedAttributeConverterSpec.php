<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Component\Localization\Exception\FormatLocalizerException;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Pim\Component\Localization\Localizer\LocalizerRegistryInterface;
use Prophecy\Argument;

class LocalizedAttributeConverterSpec extends ObjectBehavior
{

    function let(LocalizerRegistryInterface $localizerRegistry, AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($localizerRegistry, $attributeRepository);
    }

    function it_is_a_converter()
    {
        $this->shouldImplement('Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface');
    }

    function it_converts_a_number($localizerRegistry, $attributeRepository, LocalizerInterface $localizer)
    {
        $options = ['decimal_separator' => ','];
        $attributeRepository->getAttributeTypeByCodes(['number'])->willReturn(['number' => 'pim_number']);
        $localizerRegistry->getLocalizer('pim_number')->willReturn($localizer);
        $localizer->supports('pim_number')->willReturn(true);
        $localizer->isValid('10,45', $options, 'number')->willReturn(true);
        $localizer->convertLocalizedToDefault('10,45', $options)->willReturn('10.45');

        $this->convertLocalizedToDefaultValues(['number' => [['data' => '10,45']]], $options)
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

        $this->convertLocalizedToDefaultValues(['family' => [['data' => 'boots']]], $options)
            ->shouldReturn(['family' => [['data' => 'boots']]]);
    }

    function it_throws_an_exception_if_decimal_separator_is_not_expected(
        $localizerRegistry,
        $attributeRepository,
        LocalizerInterface $localizer
    ) {
        $options = ['decimal_separator' => '.'];
        $attributeRepository->getAttributeTypeByCodes(['number'])->willReturn(['number' => 'pim_number']);
        $localizerRegistry->getLocalizer('pim_number')->willReturn($localizer);
        $localizer->supports('pim_number')->willReturn(true);
        $localizer->isValid('10,45', $options, 'number')->willThrow(new FormatLocalizerException('number', '.'));
        $localizer->convertLocalizedToDefault('10,45', $options)->willReturn('10.45');

        $this->shouldThrow(new FormatLocalizerException('number', '.'))
            ->during('convertLocalizedToDefaultValues', [['number' => [['data' => '10,45']]], $options]);
    }
}
