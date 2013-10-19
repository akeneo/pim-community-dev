<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

trait FlexibleFilterTrait
{
    /**
     * Gets flexible manager
     *
     * @param FlexibleManagerRegistry $registry
     * @param string                  $flexibleEntityName
     *
     * @throws \LogicException
     * @return FlexibleManager
     */
    protected function getFlexibleManager(FlexibleManagerRegistry $registry, $flexibleEntityName)
    {
        if (!$flexibleEntityName) {
            throw new \LogicException('Flexible entity filter must have flexible entity name.');
        }
        return $registry->getManager($flexibleEntityName);
    }
}
