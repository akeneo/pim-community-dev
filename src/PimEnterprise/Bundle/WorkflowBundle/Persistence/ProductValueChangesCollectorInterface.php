<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Persistence;

/**
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ProductValueChangesCollectorInterface
{
    /**
     * Get the collected changes sent to the product values
     *
     * @return array
     */
    public function getChanges();
}
