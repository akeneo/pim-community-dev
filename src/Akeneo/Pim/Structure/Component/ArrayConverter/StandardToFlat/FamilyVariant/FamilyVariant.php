<?php

namespace Akeneo\Pim\Structure\Component\ArrayConverter\StandardToFlat\FamilyVariant;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\StandardToFlat\AbstractSimpleArrayConverter;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FamilyVariant extends AbstractSimpleArrayConverter implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     */
    protected function convertProperty($property, $data, array $convertedItem, array $options): array
    {
        switch ($property) {
            case 'labels':
                foreach ($data as $localeCode => $label) {
                    $labelKey = sprintf('label-%s', $localeCode);
                    $convertedItem[$labelKey] = $label;
                }
                break;
            case 'variant_attribute_sets':
                foreach ($data as $normalizedAttributeSet) {
                    $axesKey = sprintf('variant-axes_%d', $normalizedAttributeSet['level']);
                    $attributesKey = sprintf('variant-attributes_%d', $normalizedAttributeSet['level']);
                    $convertedItem[$axesKey] = implode(',', $normalizedAttributeSet['axes']);
                    $convertedItem[$attributesKey] = implode(',', $normalizedAttributeSet['attributes']);
                }
                break;
            default:
                $convertedItem[$property] = (string) $data;
        }

        return $convertedItem;
    }
}
