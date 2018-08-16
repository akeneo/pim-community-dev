<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Comparator\Field;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;

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
        if (null !== $originals && (bool) $originals === (bool) $data) {
            return null;
        }

        return $data;
    }
}
