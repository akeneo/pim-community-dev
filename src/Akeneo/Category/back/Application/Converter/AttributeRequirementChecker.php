<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter;

use Akeneo\Category\Infrastructure\Converter\InternalAPI\InternalAPIToStd;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @phpstan-import-type AttributeValueApi from InternalAPIToStd
 */
class AttributeRequirementChecker
{
    /**
     * @param array<string, AttributeValueApi> $attributeValues
     * @param array<string> $expectedKeys
     */
    public function checkAttributeValueKeysExist(array $attributeValues, array $expectedKeys): void
    {
        $keys = array_keys($attributeValues);

        foreach ($expectedKeys as $expectedKey) {
            $pattern = '/^'.$expectedKey.'(\w+)/i';
            if (empty(preg_grep($pattern, $keys))) {
                throw new StructureArrayConversionException(sprintf('Field "%s" is expected, provided fields are "%s"', $expectedKey, implode(', ', $keys)));
            }
        }
    }

    /**
     * @param array<string, AttributeValueApi> $attributeValues
     */
    public function checkAttributeValueArrayStructure(array $attributeValues): void
    {
        foreach ($attributeValues as $key => $value) {
            $this->checkKeyExist($value, 'data');
            $this->checkKeyExist($value, 'locale');
            $this->checkKeyExist($value, 'attribute_code');

            try {
                Assert::notEmpty($value['data']);
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
    public function checkKeyExist(array $haystack, string $key): void
    {
        try {
            Assert::keyExists($haystack, $key);
        } catch (\InvalidArgumentException $exception) {
            throw new StructureArrayConversionException(sprintf('Field "%s" is expected', $key));
        }
    }
}
