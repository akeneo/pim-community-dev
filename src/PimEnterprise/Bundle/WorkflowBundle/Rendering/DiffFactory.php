<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Rendering;

/**
 * A \Diff instance factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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

        return new \Diff($before, $after, $options);
    }
}
