<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
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
        $localizer->isValid('10,45', $options)->willReturn(true);
        $localizer->convertLocalizedToDefault('10,45')->willReturn('10.45');

        $this->convert(['number' => [['data' => '10,45']]], $options)
            ->shouldReturn(['number' => [['data' => '10.45']]]);
    }

    function it_does_not_convert_a_product_field($localizerRegistry, $attributeRepository, LocalizerInterface $localizer)
    {
        $options = ['decimal_separator' => ','];
        $attributeRepository->getAttributeTypeByCodes(['family'])->willReturn([]);
        $localizerRegistry->getLocalizer('pim_family')->willReturn($localizer);
        $localizer->supports('pim_family')->willReturn(false);

        $this->convert(['family' => [['data' => 'boots']]], $options)
            ->shouldReturn(['family' => [['data' => 'boots']]]);
    }

    function it_fails_if_decimal_separator_is_not_expected(
        $localizerRegistry,
        $attributeRepository,
        LocalizerInterface $localizer
    ) {
        $options = ['decimal_separator' => '.'];
        $attributeRepository->getAttributeTypeByCodes(['number'])->willReturn(['number' => 'pim_number']);
        $localizerRegistry->getLocalizer('pim_number')->willReturn($localizer);
        $localizer->supports('pim_number')->willReturn(true);
        $localizer->isValid('10,45', $options)->willReturn(false);
        $localizer->convertLocalizedToDefault('10,45')->willReturn('10.45');

        $message = 'Format for attribute "number" is not respected. Format expected: [ "decimal_separator": "." ]';
        $this->shouldThrow(new \LogicException($message))
            ->during('convert', [['number' => [['data' => '10,45']]], $options]);
    }
}
