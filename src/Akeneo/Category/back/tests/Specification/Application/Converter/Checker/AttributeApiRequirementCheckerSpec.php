<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\AttributeApiRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeApiRequirementCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeApiRequirementChecker::class);
        $this->shouldImplement(RequirementChecker::class);
    }

    public function it_should_throw_an_exception_when_locale_composite_key_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    [
                        "data" => "",
                        "channel" => "ecommerce",
                        "locale" => "fr_FR",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_locale_composite_key_is_empty(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = "";
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    $localeCompositeKey => [
                        "data" => "",
                        "channel" => "ecommerce",
                        "locale" => "fr_FR",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_attribute_key_data_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    $localeCompositeKey => [
                        "channel" => "ecommerce",
                        "locale" => "fr_FR",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_not_throw_an_exception_when_attribute_key_data_is_empty(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this
            ->shouldNotThrow()
            ->duringCheck(
                [
                    $localeCompositeKey => [
                        "data" => "",
                        "channel" => "ecommerce",
                        "locale" => "fr_FR",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_attribute_key_channel_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'ecommerce';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    $localeCompositeKey => [
                        "data" => "Shoes",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_attribute_key_channel_is_empty(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'ecommerce';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    $localeCompositeKey => [
                        "data" => "Shoes",
                        "channel" => "",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_attribute_key_locale_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    $localeCompositeKey => [
                        "data" => "Shoes",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_attribute_key_locale_is_empty(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    $localeCompositeKey => [
                        "data" => "Shoes",
                        "locale" => "",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_attribute_code_key_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    $localeCompositeKey => [
                        "data" => "Shoes",
                        "locale" => "fr_FR"
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_attribute_code_key_is_empty(): void
    {
        $localeCompositeKey = "title"
            . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030"
            . AbstractValue::SEPARATOR . 'fr_FR';

        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    $localeCompositeKey => [
                        "data" => "Shoes",
                        "locale" => "fr_FR",
                        "attribute_code" => ""
                    ],
                ]
            );
    }
}
