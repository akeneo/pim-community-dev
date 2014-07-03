<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

/**
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ChangesCollectorAwareInterface
{
    public function setCollector(ChangesCollectorInterface $collector);
}
