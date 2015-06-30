<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for collections of options
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, ['pim_catalog_multiselect', 'pim_reference_data_multiselect']); //TODO: we should use a instance variable for extensibility purpose
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => []];
        $originals = array_merge($default, $originals);

        sort($data['value']);
        sort($originals['value']);

        $codes = [];
        foreach ($data['value'] as $index => $attribute) {
            if (!isset($originals['value'][$index]) || $attribute !== $originals['value'][$index]) {
                $codes[] = $attribute;
            }
        }

        if (empty($codes)) {
            return null;
        }

        return [
            'locale' => $data['locale'],
            'scope'  => $data['scope'],
            'value'  => $codes,
        ];
    }
}
