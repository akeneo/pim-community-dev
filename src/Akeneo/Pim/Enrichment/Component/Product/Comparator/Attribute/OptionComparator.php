<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for options
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionComparator implements ComparatorInterface
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
        return \in_array($type, $this->types);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'data' => null];
        $originals = array_merge($default, $originals);

        if (null === $data['data'] && null === $originals['data']) {
            return null;
        }

        if (!\is_string($data['data'])) {
            return $data;
        }

        if (\strtolower($data['data']) === \strtolower($originals['data'])) {
            return null;
        }

        return $data;
    }
}
