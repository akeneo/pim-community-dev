<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter\Checker;

use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Value from ValueCollection
 * @phpstan-import-type AttributeCode from ValueCollection
 * @phpstan-import-type ImageValue from ValueCollection
 */
class ValueCollectionRequirementChecker implements RequirementChecker
{
    /**
     * @param array<string, AttributeCode|Value> $data (example :["attribute_codes" => ["code|uuid"], "code|uuid|locale" => ["data" => [], "locale" => "en_US", "attribute_code" => "code|uuid"]])
     *
     * @throws ArrayConversionException
     */
    public function check(array $data): void
    {
        self::checkValues($data);
    }

    /**
     * @param array<string, AttributeCode|Value> $attributes
     *
     * @throws ArrayConversionException
     */
    public static function checkValues(array $attributes): void
    {
        if (empty($attributes)) {
            return;
        }

        self::assertKeyExist($attributes, 'attribute_codes');

        $attributeValues = array_filter($attributes, function ($attributeKey) {
            return $attributeKey !== 'attribute_codes';
        }, ARRAY_FILTER_USE_KEY);

        $localCompositeKeys = array_keys($attributeValues);

        self::assertCompositeKeysExist($localCompositeKeys, $attributes['attribute_codes']);

        self::assertLocalCompositeKeysExist($localCompositeKeys, $attributes['attribute_codes']);

        self::assertValueArrayStructure($attributeValues);
    }

    /**
     * @param array<string, mixed> $haystack
     */
    private static function assertKeyExist(array $haystack, string $key): void
    {
        try {
            Assert::keyExists($haystack, $key);
        } catch (InvalidArgumentException $exception) {
            throw new StructureArrayConversionException(sprintf('Field "%s" is expected', $key));
        }
    }

    /**
     * @param array<string> $localCompositeKeys (example : ["title|87939c45-1d85-4134-9579-d594fff65030|en_US"])
     * @param array<string> $compositeKeys (example : ["title|87939c45-1d85-4134-9579-d594fff65030"])
     */
    private static function assertCompositeKeysExist(array $localCompositeKeys, array $compositeKeys): void
    {
        if (!empty($localCompositeKeys) && empty($compositeKeys)) {
            throw new StructureArrayConversionException('Missing Composite key in "attribute_codes"');
        }
    }

    /**
     * @param array<string> $localCompositeKeys (example : ["title|87939c45-1d85-4134-9579-d594fff65030|en_US"])
     * @param array<string> $compositeKeys (example : ["title|87939c45-1d85-4134-9579-d594fff65030"])
     */
    private static function assertLocalCompositeKeysExist(array $localCompositeKeys, array $compositeKeys): void
    {
        foreach ($compositeKeys as $expectedKey) {
            $result = array_filter($localCompositeKeys, static function ($localCompositeKey) use ($expectedKey) {
                return str_starts_with($localCompositeKey, $expectedKey);
            });

            if (empty($result)) {
                throw new StructureArrayConversionException(sprintf('Field "%s" is expected, provided fields are "%s"', $expectedKey, implode(', ', $localCompositeKeys)));
            }
        }
    }

    /**
     * @param array<string, Value> $values
     */
    private static function assertValueArrayStructure(array $values): void
    {
        foreach ($values as $key => $value) {
            self::assertKeyExist($value, 'data');
            self::assertKeyExist($value, 'locale');
            self::assertKeyExist($value, 'attribute_code');

            self::assertValueData($value['data']);

            try {
                Assert::nullOrStringNotEmpty($value['locale']);
                Assert::notEmpty($value['attribute_code']);
            } catch (InvalidArgumentException $exception) {
                throw new StructureArrayConversionException(sprintf('No empty value is expected, provided empty value for %s', $key));
            }
        }
    }

    /**
     * @param ImageValue|string|null $data
     *
     * @throws StructureArrayConversionException
     */
    private static function assertValueData(array|string|null $data): void
    {
        try {
            match (true) {
                is_null($data), is_array($data) => self::assertImageData($data),
                default => self::assertTextData($data),
            };
        } catch (InvalidArgumentException $exception) {
            throw new StructureArrayConversionException($exception->getMessage());
        }
    }

    /**
     * @param ImageValue|null $imageData
     *
     * @throws StructureArrayConversionException|InvalidArgumentException
     */
    private static function assertImageData(?array $imageData): void
    {
        if (null === $imageData) {
            return;
        }

        self::assertKeyExist($imageData, 'size');
        self::assertKeyExist($imageData, 'extension');
        self::assertKeyExist($imageData, 'file_path');
        self::assertKeyExist($imageData, 'mime_type');
        self::assertKeyExist($imageData, 'original_filename');

        Assert::integer($imageData['size'], 'Expected Integer for key [size]');
        $message = 'Expected String and not empty value for key ';
        Assert::stringNotEmpty($imageData['extension'], $message.' [extension]');
        Assert::stringNotEmpty($imageData['file_path'], $message.' [file_path]');
        Assert::stringNotEmpty($imageData['mime_type'], $message.' [mime_type]');
        Assert::stringNotEmpty($imageData['original_filename'], $message.' [original_filename]');
    }

    /**
     * @throws StructureArrayConversionException|InvalidArgumentException
     */
    private static function assertTextData(string $textData): void
    {
        Assert::string($textData, "Expected String value for 'data'");
    }
}
