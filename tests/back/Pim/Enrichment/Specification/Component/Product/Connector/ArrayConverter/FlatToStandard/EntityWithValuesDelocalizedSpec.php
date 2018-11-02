<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
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

        $this->shouldThrow(DataArrayConversionException::class)
            ->during('convert', [['the item'], ['the options']]);
    }
}
