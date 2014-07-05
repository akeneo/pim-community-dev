<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Proposition;

/**
 * Represent a class that is aware of the changes collector
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ChangesCollectorAwareInterface
{
    /**
     * Set the collector
     *
     * @param ChangesCollectorInterface $collector
     */
    public function setCollector(ChangesCollectorInterface $collector);
}
