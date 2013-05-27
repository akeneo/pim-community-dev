<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\Behavior;

/**
 * Scopable interface, implemented by class which can be scoped
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
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
