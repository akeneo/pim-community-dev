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
    public function create($a, $b, array $options = [])
    {
        $a = is_array($a) ? $a : [$a];
        $b = is_array($b) ? $b : [$b];

        return new \Diff($a, $b, $options);
    }
}
