<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Convert flat format to standard format for attribute
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeStandardConverter implements StandardArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     */
    public function convert(array $item, array $options = [])
    {
        $this->validate($item);

        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            $fields = [
                'localizable',
                'useable_as_grid_filter',
                'unique',
                'required',
                'scopable',
                'wysiwyg_enabled',
                'decimals_allowed',
                'negative_allowed',
            ];

            $convertedItem = $this->convertFields($field, $fields, $data, $convertedItem);
        }

        return $convertedItem;
    }

    /**
     * @param array $item
     */
    protected function validate(array $item)
    {
        $this->validateRequiredFields($item, ['code']);
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
                        $requiredField,
                        implode(', ', array_keys($item))
                    )
                );
            }
        }
    }

    /**
     * @param string $field
     * @param array  $fields
     * @param array  $data
     * @param array  $convertedItem
     *
     * @return array
     */
    protected function convertFields($field, $fields, $data, $convertedItem)
    {
        switch ($field) {
            case false !== strpos($field, 'label-', 0):
                $labelTokens = explode('-', $field);
                $labelLocale = $labelTokens[1];
                $convertedItem['labels'][$labelLocale] = $data;
                break;
            case 'type':
                $convertedItem['attributeType'] = $data;
                break;
            case 'number_min':
            case 'number_max':
            case 'max_file_size':
                $convertedItem[$field] = (float) $data;
                break;
            case 'sort_order':
            case 'max_characters':
                $convertedItem[$field] = (int) $data;
                break;
            case 'options':
            case 'available_locales':
                $convertedItem[$field] = explode(',', $data);
                break;
            case in_array($field, $fields):
                $convertedItem[$field] = (bool) $data;
                break;
            default:
                $convertedItem[$field] = (string) $data;
        }

        return $convertedItem;
    }
}
