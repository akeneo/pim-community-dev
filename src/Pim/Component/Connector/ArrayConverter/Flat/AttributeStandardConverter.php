<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Convert flat format to standard format for attribute
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeStandardConverter implements StandardArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /**
     * @param FieldsRequirementChecker $fieldChecker
     */
    public function __construct(FieldsRequirementChecker $fieldChecker)
    {
        $this->fieldChecker = $fieldChecker;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code']);
        $this->fieldChecker->checkFieldsFilling($item, ['code']);

        $convertedItem = ['labels' => []];
        foreach ($item as $field => $data) {
            $booleanFields = [
                'localizable',
                'useable_as_grid_filter',
                'unique',
                'required',
                'scopable',
                'wysiwyg_enabled',
                'decimals_allowed',
                'negative_allowed',
            ];

            $convertedItem = $this->convertFields($field, $booleanFields, $data, $convertedItem);
        }

        return $convertedItem;
    }

    /**
     * @param string $field
     * @param array  $booleanFields
     * @param array  $data
     * @param array  $convertedItem
     *
     * @return array
     */
    protected function convertFields($field, $booleanFields, $data, $convertedItem)
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
                $convertedItem[$field] = '' === $data ? null : (float) $data;
                break;
            case 'sort_order':
            case 'max_characters':
                $convertedItem[$field] = (int) $data;
                break;
            case 'options':
            case 'available_locales':
                $convertedItem[$field] = '' === $data ? [] : explode(',', $data);
                break;
            case in_array($field, $booleanFields):
                $convertedItem[$field] = (bool) $data;
                break;
            case 'reference_data_name':
                $convertedItem[$field] = '' === $data ? null : $data;
                break;
            default:
                $convertedItem[$field] = (string) $data;
        }

        return $convertedItem;
    }
}
