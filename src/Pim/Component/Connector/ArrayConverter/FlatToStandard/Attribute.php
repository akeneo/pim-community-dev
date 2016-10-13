<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * Convert flat format to standard format for attribute
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Attribute implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /** @var array */
    protected $booleanFields;

    /**
     * @param FieldsRequirementChecker $fieldChecker
     * @param array                    $booleanFields
     */
    public function __construct(FieldsRequirementChecker $fieldChecker, array $booleanFields)
    {
        $this->fieldChecker = $fieldChecker;
        $this->booleanFields = $booleanFields;
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
            $convertedItem = $this->convertFields($field, $this->booleanFields, $data, $convertedItem);
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
        if (false !== strpos($field, 'label-', 0)) {
            $labelTokens = explode('-', $field);
            $labelLocale = $labelTokens[1];
            $convertedItem['labels'][$labelLocale] = $data;
        } elseif ('type' === $field ) {
            $convertedItem['attribute_type'] = $data;
        } elseif ('number_min' === $field ||
            'number_max' === $field ||
            'max_file_size'=== $field
        ) {
            $convertedItem[$field] = $this->convertFloat($data);
        } elseif ('sort_order' === $field ||
            'max_characters' === $field ||
            'minimum_input_length'=== $field
        ) {
            $convertedItem[$field] = ('' === $data) ? null : (int) $data;
        } elseif ('options' === $field ||
            'available_locales' === $field ||
            'allowed_extensions'=== $field
        ) {
            $convertedItem[$field] = ('' === $data) ? [] : explode(',', $data);
        } elseif ('date_min' === $field ||
            'date_max'=== $field
        ) {
            $convertedItem[$field] = $this->convertDate($data);
        } elseif (in_array($field, $booleanFields, true)) {
            $convertedItem[$field] = (bool) $data;
        } elseif ('metric_family' === $field || '' !== $data) {
            $convertedItem[$field] = (string) $data;
        } else {
            $convertedItem[$field] = null;
        }

        return $convertedItem;
    }

    /**
     * @param mixed $number
     *
     * @return string|null
     */
    protected function convertFloat($number)
    {
        if ('' === $number || null === $number) {
            return null;
        }

        if (is_numeric($number)) {
            return number_format($number, 4, '.', '');
        }

        return $number;
    }

    /**
     * @param mixed $date
     *
     * @return string|null
     */
    protected function convertDate($date)
    {
        if ('' === $date || null === $date) {
            return null;
        }

        try {
            $datetime = new \DateTime($date);

            return $datetime->format('c');
        } catch (\Exception $e) {
            return $date;
        }
    }
}
