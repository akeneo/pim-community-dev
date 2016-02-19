<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Channel Flat to Standard format Converter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelStandardConverter implements StandardArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'code'         => 'ecommerce',
     *      'label'        => 'Ecommerce',
     *      'locales'      => 'en_US,fr_FR',
     *      'currencies'   => 'EUR,USD',
     *      'tree'         => 'master_catalog',
     *      'color'        => 'orange'
     * ]
     *
     * After:
     * [
     *     'code'   => 'ecommerce',
     *     'label'  => 'Ecommerce',
     *     'locales'    => ['en_US', 'fr_FR'],
     *     'currencies' => ['EUR', 'USD'],
     *     'tree'       => 'master_catalog',
     *     'color'      => 'orange'
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validateRequiredFields($item, ['code', 'tree', 'locales', 'currencies']);

        $convertedItem = [];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($convertedItem, $field, $data);
        }

        return $convertedItem;
    }

    /**
     * @param array  $convertedItem
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    protected function convertField(array $convertedItem, $field, $data)
    {
        if (in_array($field, ['code', 'color', 'tree', 'label'])) {
            $convertedItem[$field] = $data;
        } else {
            $convertedItem[$field] = explode(',', $data);
        }

        return $convertedItem;
    }

    /**
     * @param array $item
     * @param array $requiredFields
     *
     * @throws ArrayConversionException
     */
    protected function validateRequiredFields(array $item, array $requiredFields)
    {
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

            if ('' === $item[$requiredField]) {
                throw new ArrayConversionException(
                    sprintf(
                        'Field "%s" must be filled',
                        $requiredField
                    )
                );
            }
        }
    }
}
