<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for scalars
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScalarComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, [
            'pim_catalog_date',
            'pim_catalog_identifier',
            'pim_catalog_number',
            'pim_catalog_text',
            'pim_catalog_textarea'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => null];
        $originals = array_merge($default, $originals);

        return (string) $data['value'] !== (string) $originals['value'] ? $data : null;
    }
}
