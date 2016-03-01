<?php

namespace Pim\Component\Connector\ArrayConverter;

use Pim\Component\Connector\Exception\ArrayConversionException;

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
     * @throws ArrayConversionException
     */
    public function checkFieldsPresence(array $item, array $fields)
    {
        foreach ($fields as $field) {
            if (!in_array($field, array_keys($item))) {
                throw new ArrayConversionException(
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
     * @throws ArrayConversionException
     */
    public function checkFieldsFilling(array $item, array $fields)
    {
        foreach ($fields as $field) {
            if ('' == $item[$field]) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" must be filled',
                        $field
                    )
                );
            }
        }
    }
}
