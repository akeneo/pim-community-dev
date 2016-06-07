<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Standard to flat array converter for attribute
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Attribute implements ArrayConverterInterface
{
    /** @var array */
    protected $booleanFields;

    /**
     * @param array $booleanFields
     */
    public function __construct(array $booleanFields)
    {
        $this->booleanFields = $booleanFields;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [];

        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($field, $this->booleanFields, $data, $convertedItem);
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
    protected function convertField($field, array $booleanFields, $data, array $convertedItem)
    {
        switch ($field) {
            case 'labels':
                foreach ($data as $localeCode => $label) {
                    $labelKey = sprintf('label-%s', $localeCode);
                    $convertedItem[$labelKey] = $label;
                }
                break;
            case 'attributeType':
                $convertedItem['type'] = $data;
                break;
            case 'options':
            case 'available_locales':
                $convertedItem[$field] = implode(',', $data);
                break;
            case in_array($field, $booleanFields):
                $convertedItem[$field] = (true === $data) ? '1' : '0';
                break;
            default:
                $convertedItem[$field] = (string) $data;
        }

        return $convertedItem;
    }
}
