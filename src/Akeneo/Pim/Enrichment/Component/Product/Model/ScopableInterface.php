<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

/**
 * Scopable interface, implemented by class which can be scoped
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ScopableInterface
{
    /**
     * Get used scope
     *
     * @return string $scope
     */
    public function getScope();

    /**
     * Set used scope
     *
     * @param string $scope
     *
     * @return ScopableInterface
     */
    public function setScope($scope);
}
