<?php

namespace spec\Pim\Component\Localization\Localizer;

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Localization\Localizer\LocalizerInterface;
use Pim\Component\Localization\Localizer\LocalizerRegistryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

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
        $localizer->validate('10,45', $options, 'values[number]')->willReturn(null);
        $localizer->delocalize('10,45', $options)->willReturn('10.45');

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
        $localizer->validate('10,45', $options, 'values[number]')->willReturn($constraints);
        $localizer->delocalize('10,45', $options)->willReturn('10.45');

        $this->convertLocalizedToDefaultValues(['number' => [['data' => '10,45']]], $options)
            ->shouldReturn(['number' => [['data' => '10.45']]]);

        $violations = $this->getViolations()->getWrappedObject();
        if (null === $violations) {
            throw new FailureException('Violations should not be null');
        }
    }
}
