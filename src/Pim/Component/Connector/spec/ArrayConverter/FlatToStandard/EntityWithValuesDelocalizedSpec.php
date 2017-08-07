<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class EntityWithValuesDelocalizedSpec extends ObjectBehavior
{
    function let(ArrayConverterInterface $converter, AttributeConverterInterface $delocalizer)
    {
        $this->beConstructedWith($converter, $delocalizer);
    }

    function it_converts($converter, $delocalizer, ConstraintViolationListInterface $violations)
    {
        $converter->convert(['values' => ['the item']], ['the options'])
            ->willReturn(['values' => ['the item standardized']]);
        $delocalizer->convertToDefaultFormats(['the item standardized'], ['the options'])
            ->willReturn(['the item standardized and delocalized']);

        $delocalizer->getViolations()->willReturn($violations);
        $violations->count()->willReturn(0);

        $this->convert(['values' => ['the item']], ['the options'])->shouldReturn(['values' => ['the item standardized and delocalized']]);
    }

    function it_throws_an_exception_in_case_of_conversion_error(
        $converter,
        $delocalizer,
        ConstraintViolationListInterface $violations
    ) {
        $converter->convert(['the item'], ['the options'])->willReturn(['the item standardized']);
        $delocalizer->convertToDefaultFormats(['the item standardized'], ['the options'])
            ->willReturn(['the item standardized and delocalized']);

        $delocalizer->getViolations()->willReturn($violations);
        $violations->count()->willReturn(3);

        $this->shouldThrow('Pim\Component\Connector\Exception\DataArrayConversionException')
            ->during('convert', [['the item'], ['the options']]);
    }
}
