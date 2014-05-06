<?php

namespace PimEnterprise\Bundle\CatalogBundle\Persistence\Engine;

/**
 * Compute multidimensional arrays differences
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ArrayDiff
{
    /**
     * Get the differences in $a compare to $b
     *
     * @param array $a
     * @param array $b
     * @param array $diffs
     *
     * @return array
     */
    public function diff(array $a, array $b, array $diffs = array())
    {
        if (empty($a)) {
            return $diffs + $b;
        }

        $head = $this->head($a);

        if (!array_key_exists($head, $b)) {
            $diffs[$head] = null;
        } else if (is_array($a[$head]) && [] !== $diff = $this->diff($a[$head], $b[$head])) {
            $diffs[$head] = $diff;
        } else if ($a[$head] != $b[$head]) {
            $diffs[$head] = $b[$head];
        }

        return $this->diff(
            $this->tail($a),
            $this->tail($b),
            $diffs
        );
    }

    /**
     * Get the first key of the array
     *
     * @param array $a
     *
     * @return int|string
     */
    private function head(array $a)
    {
        reset($a);

        return key($a);
    }

    /**
     * Get the remaining elements of the array without the head
     *
     * @param array $a
     *
     * @return array
     */
    private function tail(array $a)
    {
        unset($a[$this->head($a)]);

        return $a;
    }
}
