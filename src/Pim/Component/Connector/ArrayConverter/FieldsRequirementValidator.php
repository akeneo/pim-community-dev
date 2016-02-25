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
class FieldsRequirementValidator
{
    /**
     * Validates if all the required fields are present and check if they are filled.
     *
     * @param array $item
     * @param array $requiredFields
     * @param bool  $checkFilling
     *
     * @throws ArrayConversionException
     */
    public function validateFields(array $item, array $requiredFields, $checkFilling = true)
    {
        foreach ($requiredFields as $requiredField) {
            $this->validateFieldPresence($item, $requiredField);
            if ($checkFilling) {
                $this->validateFieldFilling($item, $requiredField);
            }
        }
    }

    /**
     * Validate if the field is present
     *
     * @param array  $item
     * @param string $field
     *
     * @throws ArrayConversionException
     */
    protected function validateFieldPresence(array $item, $field)
    {
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

    /**
     * Validate if the field is filled
     *
     * @param array  $item
     * @param string $field
     *
     * @throws ArrayConversionException
     */
    protected function validateFieldFilling(array $item, $field)
    {
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
