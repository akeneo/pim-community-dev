<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Converter;

use Akeneo\Category\Application\Converter\AttributeRequirementChecker;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeRequirementCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeRequirementChecker::class);
    }

    public function it_should_throw_an_exception_when_attribute_keys_is_missing(): void
    {
        $missingKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$missingKey],
                    'title' . ValueCollection::SEPARATOR . 'fr_FR' => [
                        "data" => "Les chaussures dont vous avez besoin !",
                        "locale" => "fr_FR",
                        "attribute_code" => $missingKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_attribute_values_are_not_well_structured(): void
    {
        $identifier = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";;
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$identifier],
                    $identifier . 'fr_FR' => [
                        "data" => "Les chaussures dont vous avez besoin !",
                        "locale" => "fr_FR",
                        "attribute_code" => "title_87939c45-1d85-4134-9579-d594fff65030"
                    ],
                    $identifier . "en_US" => [
                        "data" => "All the shoes you need!",
                        "locale" => "en_US",
                    ],
                ]
            );
    }
}
