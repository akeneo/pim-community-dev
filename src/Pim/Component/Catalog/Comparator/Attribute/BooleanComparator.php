<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for booleans
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, [//TODO: we should use an instance variable for extensibility purpose
            'pim_catalog_boolean',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => null];
        $originals = array_merge($default, $originals);

        $isNull = null === $originals['value'] && null === $data['value'];
        $isEquals = (bool) $originals['value'] === (bool) $data['value'];

        if ($isNull || $isEquals) {
            return null;
        }

        return $data;
    }
}
