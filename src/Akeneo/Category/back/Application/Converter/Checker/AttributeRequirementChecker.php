<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter\Checker;

use Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd;
use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type AttributeValueApi from InternalApiToStd
 * @phpstan-import-type AttributeCodeApi from InternalApiToStd
 */
class AttributeRequirementChecker implements RequirementChecker
{
    /**
     * @param array<string, AttributeCodeApi|AttributeValueApi> $data
     *
     * @throws ArrayConversionException
     */
    public function check(array $data): void
    {
        self::checkAttributes($data);
    }

    /**
     * @param array<string, AttributeCodeApi|AttributeValueApi> $attributes
     *
     * @throws ArrayConversionException
     */
    public static function checkAttributes(array $attributes): void
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

        self::assertAttributeValueArrayStructure($attributeValues);
    }

    /**
     * @param array<string, mixed> $haystack
     */
    private static function assertKeyExist(array $haystack, string $key): void
    {
        try {
            Assert::keyExists($haystack, $key);
        } catch (\InvalidArgumentException $exception) {
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
     * @param array<string, AttributeValueApi> $attributeValues
     */
    private static function assertAttributeValueArrayStructure(array $attributeValues): void
    {
        foreach ($attributeValues as $key => $value) {
            self::assertKeyExist($value, 'data');
            self::assertKeyExist($value, 'locale');
            self::assertKeyExist($value, 'attribute_code');

            try {
                Assert::notEmpty($value['data']);
                Assert::nullOrStringNotEmpty($value['locale']);
                Assert::notEmpty($value['attribute_code']);
            } catch (\InvalidArgumentException $exception) {
                throw new StructureArrayConversionException(sprintf('No empty value is expected, provided empty value for %s', $key));
            }
        }
    }
}
