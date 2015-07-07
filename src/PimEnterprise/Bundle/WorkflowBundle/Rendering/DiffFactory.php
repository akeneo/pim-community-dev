<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Rendering;

/**
 * A \Diff instance factory
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class DiffFactory
{
    /**
     * Instanciate a configured Diff
     *
     * @param string|array $before
     * @param string|array $after
     * @param array        $options
     *
     * @return \Diff
     */
    public function create($before, $after, array $options = [])
    {
        $before = is_array($before) ? $before : [$before];
        $after = is_array($after) ? $after : [$after];

        $uniqueBefore = array_values(array_diff($before, $after));
        $uniqueAfter = array_values(array_diff($after, $before));

        return new \Diff($uniqueBefore, $uniqueAfter, $options);
    }
}
