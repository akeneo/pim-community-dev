<?php

namespace Akeneo\Pim\Structure\Component\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

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
     * @param mixed  $data
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
            'allowed_extensions' === $field
        ) {
            $convertedItem[$field] = ('' === $data) ? [] : explode(',', $data);
        } elseif ('date_min' === $field ||
            'date_max'=== $field
        ) {
            $convertedItem[$field] = $this->convertDate($data);
        } elseif (in_array($field, $booleanFields, true) && '' !== $data) {
            $convertedItem[$field] = (bool) $data;
        } elseif ('' !== $data) {
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
     * Return the value if it's not a date (launch an exception should not be done here).
     * "2015-12-31" will be converted to "2015-12-31T00:00:00+01:00"
     *
     * These dates are wrong and will not converted:
     * "2015/12/31"
     * "2015-45-31"
     * "not a date"
     *
     * @param mixed $date
     *
     * @return string|null
     */
    protected function convertDate($date)
    {
        if ('' === $date || null === $date) {
            return null;
        }

        $datetime = \DateTime::createFromFormat('Y-m-d', $date);
        $errors = \DateTime::getLastErrors();

        if (0 === $errors['warning_count'] && 0 === $errors['error_count']) {
            $datetime->setTime(0, 0, 0);

            return $datetime->format('c');
        }

        return $date;
    }
}
