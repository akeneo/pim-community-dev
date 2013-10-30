<?php

namespace Pim\Bundle\GridBundle\Route;

use Oro\Bundle\GridBundle\Route\DefaultRouteGenerator;

/**
 * Overriden to allow setting the route name
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetterRouteGenerator extends DefaultRouteGenerator
{
    /**
     * Set the route name
     *
     * @param string $routeName
     *
     * @return null
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }
}
