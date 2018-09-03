<?php

namespace Akeneo\Channel\Component\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * Channel Flat to Standard format Converter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Channel implements ArrayConverterInterface
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
     *
     * Before:
     * [
     *      'code'             => 'mobile',
     *      'label-fr_FR'      => 'Mobile',
     *      'label-en_US'      => 'Mobil',
     *      'locales'          => 'en_US,fr_FR',
     *      'currencies'       => 'EUR,USD',
     *      'tree'             => 'master_catalog',
     *      'conversion_units' => 'weight: GRAM,maximum_scan_size:KILOMETER, display_diagonal:DEKAMETER, viewing_area: DEKAMETER'
     * ]
     *
     * After:
     * [
     *     'code'             => 'ecommerce',
     *     'labels' => [
     *          'fr_FR' => 'Mobile',
     *          'en_US' => 'Mobil',
     *      ],
     *     'locales'          => ['en_US', 'fr_FR'],
     *     'currencies'       => ['EUR', 'USD'],
     *     'category_tree'    => 'master_catalog',
     *     'conversion_units' => [
     *          'weight'            => 'GRAM',
     *          'maximum_scan_size' => 'KILOMETER',
     *          'display_diagonal'  => 'DEKAMETER',
     *          'viewing_area'      => 'DEKAMETER'
     *      ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code', 'tree', 'locales', 'currencies']);
        $this->fieldChecker->checkFieldsFilling($item, ['code', 'tree', 'locales', 'currencies']);

        $convertedItem = ['labels' => []];
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
        if (false !== strpos($field, 'label-', 0)) {
            $labelTokens = explode('-', $field);
            $labelLocale = $labelTokens[1];
            $convertedItem['labels'][$labelLocale] = $data;
        } elseif ('locales' === $field || 'currencies' === $field) {
            $convertedItem[$field] = explode(',', $data);
        } elseif ('conversion_units' === $field) {
            $convertedItem[$field] = $this->convertUnits($data);
        } elseif ('tree' === $field) {
            $convertedItem['category_tree'] = $data;
        } else {
            $convertedItem[$field] = $data;
        }

        return $convertedItem;
    }

    /**
     * @param array $flatUnits
     *
     * @return array
     */
    protected function convertUnits($flatUnits)
    {
        $units = array_filter(explode(',', $flatUnits));

        $formattedUnits = [];
        foreach ($units as $unit) {
            list($key, $value) = explode(':', trim($unit));
            $formattedUnits[trim($key)] = trim($value);
        }

        return $formattedUnits;
    }
}
