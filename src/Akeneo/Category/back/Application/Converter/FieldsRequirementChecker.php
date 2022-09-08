<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter;

use Akeneo\Category\Infrastructure\Exception\ContentArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldsRequirementChecker
{
    /**
     * Checks whether all required fields are present.
     *
     * @param array<string> $item
     * @param array<string, mixed> $fields
     */
    public function checkFieldsExist(array $item, array $fields): void
    {
        foreach ($fields as $field) {
            if (!array_key_exists($field, $item)) {
                throw new StructureArrayConversionException(sprintf('Field "%s" is expected, provided fields are "%s"', $field, implode(', ', array_keys($item))));
            }
        }
    }

    /**
     * Checks that fields provided are not empty.
     *
     * @param array<string> $item
     * @param array<string, mixed> $fields
     */
    public function checkFieldsNotEmpty(array $item, array $fields): void
    {
        foreach ($fields as $field) {
            if ('' == $item[$field]) {
                throw new ContentArrayConversionException(sprintf('Field "%s" must not be empty', $field));
            }
        }
    }

    /**
     * Check that if a category has a parent, the parent is not the category itself.
     * (not used right now but will be useful in the future when dealing with a category's position in a tree).
     */
    public function checkParentAutoReference(string $code, string $parentCode): void
    {
        if ($code === $parentCode) {
            throw new StructureArrayConversionException(sprintf('The parent category of "%s" must be different from the category itself', $code));
        }
    }
}
