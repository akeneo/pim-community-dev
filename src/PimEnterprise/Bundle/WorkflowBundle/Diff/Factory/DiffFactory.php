<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Diff\Factory;

/**
 * A \Diff instance factory
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
