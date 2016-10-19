<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Convert standard format to flat format for family
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Family extends AbstractSimpleArrayConverter implements ArrayConverterInterface
{
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
            case 'attribute_requirements':
                foreach ($data as $scopeCode => $attributes) {
                    $requirementKey = sprintf('requirements-%s', $scopeCode);
                    $convertedItem[$requirementKey] = implode(',', $attributes);
                }
                break;
            case 'attributes':
                $convertedItem[$property] = implode(',', $data);
                break;
            default:
                $convertedItem[$property] = (string) $data;
        }

        return $convertedItem;
    }
}
