<?php

namespace Akeneo\Tool\Component\Connector\ArrayConverter;

use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;

/**
 * Simple validator to check if required fields are present and filled.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldsRequirementChecker
{
    /**
     * Validates if all the required fields are present.
     *
     * @param array $item
     * @param array $fields
     *
     * @throws StructureArrayConversionException
     */
    public function checkFieldsPresence(array $item, array $fields)
    {
        foreach ($fields as $field) {
            if (!in_array($field, array_keys($item))) {
                throw new StructureArrayConversionException(
                    sprintf(
                        'Field "%s" is expected, provided fields are "%s"',
                        $field,
                        implode(', ', array_keys($item))
                    )
                );
            }
        }
    }

    /**
     * Check that all fields are filled.
     *
     * @param array $item
     * @param array $fields
     *
     * @throws DataArrayConversionException
     */
    public function checkFieldsFilling(array $item, array $fields)
    {
        foreach ($fields as $field) {
            if ('' == $item[$field]) {
                throw new DataArrayConversionException(
                    sprintf(
                        'Field "%s" must be filled',
                        $field
                    )
                );
            }
        }
    }
}
