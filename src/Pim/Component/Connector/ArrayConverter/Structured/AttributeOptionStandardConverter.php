<?php

namespace Pim\Component\Connector\ArrayConverter\Structured;

use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Convert structured format to standard format for attribute option
 *
 * @author    Nicolas Dupont <nicola@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionStandardConverter implements StandardArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts yaml array to standard structured array:
     *
     * Before:
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sortOrder': 2,
     *     'labels': {
     *         'de_DE': '210 x 1219 mm',
     *         'en_US': '210 x 1219 mm',
     *         'fr_FR': '210 x 1219 mm'
     *     }
     * }
     *
     * After:
     * {
     *     'attribute': 'maximum_print_size',
     *     'code': '210_x_1219_mm',
     *     'sort_order': 2,
     *     'labels': {
     *         'de_DE': '210 x 1219 mm',
     *         'en_US': '210 x 1219 mm',
     *         'fr_FR': '210 x 1219 mm'
     *     }
     * }
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);
        $item['sort_order'] = $item['sortOrder'];
        unset($item['sortOrder']);

        return $item;
    }

    /**
     * @param array $item
     *
     * @throws ArrayConversionException
     */
    protected function validate(array $item)
    {
        $requiredFields = ['attribute', 'code'];
        foreach ($requiredFields as $requiredField) {
            if (!in_array($requiredField, array_keys($item))) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" is expected, provided fields are "%s"',
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }
        }

        $authorizedFields = array_merge($requiredFields, ['sortOrder', 'labels']);
        foreach ($item as $field => $data) {
            if (!in_array($field, $authorizedFields)) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" is provided, authorized fields are: "%s"',
                        $field,
                        implode(', ', $authorizedFields)
                    )
                );
            }
        }

        if (isset($item['labels']) && !is_array($item['labels'])) {
            throw new ArrayConversionException(
                sprintf('Field "labels" must be an array, data provided is "%s"', print_r($item['labels'], true))
            );
        }
    }
}
