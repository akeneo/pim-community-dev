<?php

namespace Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\StandardToFlat\AbstractSimpleArrayConverter;

/**
 * Convert standard format to flat format for attribute
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Attribute extends AbstractSimpleArrayConverter implements ArrayConverterInterface
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
    protected function convertProperty($property, $data, array $convertedItem, array $options)
    {
        switch ($property) {
            case 'labels':
                foreach ($data as $localeCode => $label) {
                    $labelKey = sprintf('label-%s', $localeCode);
                    $convertedItem[$labelKey] = $label;
                }
                break;
            case 'options':
            case 'available_locales':
            case 'allowed_extensions':
                $convertedItem[$property] = implode(',', $data);
                break;
            case in_array($property, $this->booleanFields):
                if (null === $data) {
                    $convertedItem[$property] = '';
                    break;
                }

                $convertedItem[$property] = (true === $data) ? '1' : '0';
                break;
            default:
                $convertedItem[$property] = (string) $data;
        }

        return $convertedItem;
    }
}
