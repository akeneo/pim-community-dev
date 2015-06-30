<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for booleans
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanComparator implements ComparatorInterface
{
    /** @var array */
    protected $types;

    /**
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return in_array($type, $this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => null];
        $originals = array_merge($default, $originals);

        $isNull = null === $originals['value'] && null === $data['value'];
        $isEquals = $originals['value'] === (bool) $data['value'];

        if ($isNull || $isEquals) {
            return null;
        }

        return $data;
    }
}
