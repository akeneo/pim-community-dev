<?php

namespace Pim\Component\Catalog\Comparator\Field;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for an array
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArrayComparator implements ComparatorInterface
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
        if (is_array($data)) {
            sort($data);
        }

        if (is_array($originals)) {
            sort($originals);
        }

        if ($originals === $data) {
            return null;
        }

        return $data;
    }
}
