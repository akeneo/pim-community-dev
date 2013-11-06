<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model\Behavior;

/**
 * Scopable interface, implemented by class which can be scoped
 */
interface ScopableInterface
{
    /**
     * Get used scope
     * @return string $scope
     */
    public function getScope();

    /**
     * Set used scope
     * @param string $scope
     *
     * @return ScopableInterface
     */
    public function setScope($scope);
}
