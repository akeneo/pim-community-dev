<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\Exception\ArrayConversionException;

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
     *      'code'             => 'ecommerce',
     *      'label'            => 'Ecommerce',
     *      'locales'          => 'en_US,fr_FR',
     *      'currencies'       => 'EUR,USD',
     *      'tree'             => 'master_catalog',
     *      'conversion_units' => 'weight: GRAM,maximum_scan_size:KILOMETER, display_diagonal:DEKAMETER, viewing_area: DEKAMETER'
     * ]
     *
     * After:
     * [
     *     'code'             => 'ecommerce',
     *     'label'            => 'Ecommerce',
     *     'locales'          => ['en_US', 'fr_FR'],
     *     'currencies'       => ['EUR', 'USD'],
     *     'tree'             => 'master_catalog',
     *     'conversion_units' => [
     *          'weight' => 'GRAM',
     *          'maximum_scan_size' => 'KILOMETER',
     *          'display_diagonal' => 'DEKAMETER',
     *          'viewing_area' => 'DEKAMETER'
     *      ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldChecker->checkFieldsPresence($item, ['code', 'tree', 'locales', 'currencies']);
        $this->fieldChecker->checkFieldsFilling($item, ['code', 'tree', 'locales', 'currencies']);

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
        switch ($field) {
            case 'locales':
            case 'currencies':
                $convertedItem[$field] = explode(',', $data);
                break;
            case 'conversion_units':
                $convertedItem[$field] = $this->convertUnits($data);
                break;
            default:
                $convertedItem[$field] = $data;
                break;
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
        $units = explode(',', $flatUnits);

        $formattedUnits = [];
        foreach ($units as $unit) {
            list($key, $value) = explode(':', trim($unit));
            $formattedUnits[trim($key)] = trim($value);
        }

        return $formattedUnits;
    }
}
