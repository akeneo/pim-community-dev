<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Application\Converter\Checker\ValueCollectionRequirementChecker;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueCollectionRequirementCheckerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ValueCollectionRequirementChecker::class);
        $this->shouldImplement(RequirementChecker::class);
    }

    public function it_should_throw_an_exception_when_local_composite_keys_is_missing(): void
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

    public function it_should_throw_an_exception_when_locale_composite_key_is_empty(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = "";
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => "Les chaussures dont vous avez besoin !",
                        "locale" => "fr_FR",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_value_key_data_is_missing(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "locale" => "fr_FR",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_not_throw_an_exception_when_key_data_for_string_value_is_empty(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldNotThrow()
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => "",
                        "locale" => "fr_FR",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_size_for_image_value_is_missing(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "extension" => "png",
                            "file_path" => "path/logo.png",
                            "mime_type" => "image/png",
                            "original_filename" => "logo.png",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_size_for_image_value_is_wrong(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "size" => '80219',
                            "extension" => "png",
                            "file_path" => "path/logo.png",
                            "mime_type" => "image/png",
                            "original_filename" => "logo.png",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_extension_for_image_value_is_missing(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "size" => 80219,
                            "file_path" => "path/logo.png",
                            "mime_type" => "image/png",
                            "original_filename" => "logo.png",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_extension_for_image_value_is_empty(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "size" => 80219,
                            "extension" => "",
                            "file_path" => "path/logo.png",
                            "mime_type" => "image/png",
                            "original_filename" => "logo.png",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_file_path_for_image_value_is_missing(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "size" => 80219,
                            "extension" => "png",
                            "mime_type" => "image/png",
                            "original_filename" => "logo.png",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_file_path_for_image_value_is_empty(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "size" => 80219,
                            "extension" => "png",
                            "file_path" => "",
                            "mime_type" => "image/png",
                            "original_filename" => "logo.png",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_mime_type_for_image_value_is_missing(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "size" => 80219,
                            "extension" => "png",
                            "file_path" => "path/logo.png",
                            "original_filename" => "logo.png",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_mime_type_for_image_value_is_empty(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "size" => '80219',
                            "extension" => "png",
                            "file_path" => "path/logo.png",
                            "mime_type" => "",
                            "original_filename" => "logo.png",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_original_filename_for_image_value_is_missing(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "size" => 80219,
                            "extension" => "png",
                            "file_path" => "path/logo.png",
                            "mime_type" => "image/png",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_key_original_filename_for_image_value_is_empty(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => [
                            "size" => '80219',
                            "extension" => "png",
                            "file_path" => "path/logo.png",
                            "mime_type" => "image/png",
                            "original_filename" => "",
                        ],
                        "locale" => null,
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_value_key_locale_is_missing(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => "Les chaussures dont vous avez besoin !",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_value_key_locale_is_empty(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => "Les chaussures dont vous avez besoin !",
                        "locale" => "",
                        "attribute_code" => $compositeKey
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_value_key_attribute_code_is_missing(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => "Les chaussures dont vous avez besoin !",
                    ],
                ]
            );
    }

    public function it_should_throw_an_exception_when_value_key_attribute_code_is_empty(): void
    {
        $compositeKey = "title" . ValueCollection::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . ValueCollection::SEPARATOR . 'fr_FR';
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->duringCheck(
                [
                    'attribute_codes' => [$compositeKey],
                    $localeCompositeKey => [
                        "data" => "Les chaussures dont vous avez besoin !",
                        "locale" => "",
                        "attribute_code" => ""
                    ],
                ]
            );
    }
}
