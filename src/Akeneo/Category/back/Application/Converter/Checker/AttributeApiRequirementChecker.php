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
 */
class AttributeApiRequirementChecker implements RequirementChecker
{
    /**
     * @param array<string, AttributeValueApi> $data
     *
     * @throws ArrayConversionException
     */
    public function check(array $data): void
    {
        if (empty($data)) {
            return;
        }

        self::assertAttributeValueArrayStructure($data);
    }

    /**
     * @param array<string, AttributeValueApi> $attributeValues
     */
    private static function assertAttributeValueArrayStructure(array $attributeValues): void
    {
        foreach ($attributeValues as $key => $value) {
            self::assertKeyExist($value, 'data');
            self::assertKeyExist($value, 'channel');
            self::assertKeyExist($value, 'locale');
            self::assertKeyExist($value, 'attribute_code');

            try {
                Assert::stringNotEmpty($key);
                Assert::nullOrStringNotEmpty($value['channel']);
                Assert::nullOrStringNotEmpty($value['locale']);
                Assert::notEmpty($value['attribute_code']);
            } catch (\InvalidArgumentException $exception) {
                throw new StructureArrayConversionException(sprintf('No empty value is expected, provided empty value for %s', $key));
            }
        }
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
}
