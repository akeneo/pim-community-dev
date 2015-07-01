<?php

namespace Pim\Component\Catalog\Comparator\Field;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for scalar
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScalarComparator implements ComparatorInterface
{
    /** @var array */
    protected $columns;

    /**
     * @param array $columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($column)
    {
        return in_array($column, $this->columns);
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        if ((string) $originals === (string) $data) {
            return null;
        }

        return $data;
    }
}
