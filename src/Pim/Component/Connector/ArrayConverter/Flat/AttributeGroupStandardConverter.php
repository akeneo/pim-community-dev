<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Channel Flat to Standard format Converter
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupStandardConverter implements StandardArrayConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *     'code'        => 'sizes',
     *     'sort_order'  => 1,
     *     'attributes'  => 'size,main_color',
     *     'label-en_US' => 'Sizes',
     *     'label-fr_FR' => 'Tailles'
     * ]
     *
     * After:
     * [
     *     'code'       => 'sizes',
     *     'sort_order' => 1,
     *     'attributes' => ['size', 'main_color'],
     *     'label'      => [
     *         'en_US' => 'Sizes',
     *         'fr_FR' => 'Tailles'
     *     ]
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validateRequiredFields($item, ['code', 'sort_order']);

        $convertedItem = [];
        foreach($item as $field => $data) {
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
        if (in_array($field, ['code', 'sort_order'])) {
            $convertedItem[$field] = $data;
        } elseif (preg_match('/^label-([\w_]+)$/', $field, $matches)) {
            if (!isset($convertedItem['label'])) {
                $convertedItem['label'] = [];
            }
            $convertedItem['label'][$matches[1]] = $data;
        } else {
            $convertedItem[$field] = explode(',', $data);
        }

        return $convertedItem;
    }

    /**
     * TODO: Should be refactored with Pim\Component\Connector\ArrayConverter\Flat\ChannelStandardConverter
     *
     * @param array $item
     * @param array $requiredFields
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

            if ('' == $item[$requiredField]) {
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
