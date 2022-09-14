<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Converter\Checker;

use Akeneo\Category\Infrastructure\Converter\InternalApi\InternalApiToStd;
use Akeneo\Category\Infrastructure\Exception\ArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\ContentArrayConversionException;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type PropertyApi from InternalApiToStd
 */
class FieldsRequirementChecker implements RequirementChecker
{
    /**
     * @param PropertyApi $data
     *
     * @throws ArrayConversionException
     */
    public function check(array $data): void
    {
        $this->checkFieldsExist($data);
        $this->checkFieldsNotEmpty($data);
    }

    /**
     * Checks whether all required fields are present.
     *
     * @param PropertyApi $data
     */
    private function checkFieldsExist(array $data): void
    {
        $fields = ['code', 'labels'];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new StructureArrayConversionException(sprintf('Field "%s" is expected, provided fields are "%s"', $field, implode(', ', array_keys($data))));
            }
        }
    }

    /**
     * Checks that fields provided are not empty.
     *
     * @param PropertyApi $data
     */
    private function checkFieldsNotEmpty(array $data): void
    {
        $fields = ['code'];
        foreach ($fields as $field) {
            if ('' == $data[$field]) {
                throw new ContentArrayConversionException(sprintf('Field "%s" must not be empty', $field));
            }
        }
    }

    /**
     * Check that if a category has a parent, the parent is not the category itself.
     * (not used right now but will be useful in the future when dealing with a category's position in a tree).
     * /!\ Do not forget to add test.
     *
     * @phpstan-ignore-next-line prevent phpstan verification due to not used function for now.
     */
    private function checkParentAutoReference(array $data): void
    {
        if ($data['code'] === $data['parent']) {
            throw new StructureArrayConversionException(sprintf('The parent category of "%s" must be different from the category itself', $data['code']));
        }
    }
}
