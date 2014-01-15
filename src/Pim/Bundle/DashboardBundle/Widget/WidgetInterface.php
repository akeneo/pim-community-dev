<?php

namespace Pim\Bundle\DashboardBundle\Widget;

/**
 * A widget view that will be used in the dashboard
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WidgetInterface
{
    /**
     * Get the template reference
     *
     * ie.: bundle:module:tamplate.format.engine
     *
     * @return string
     */
    public function getTemplate();

    /**
     * Get the template parameters
     *
     * @return array
     */
    public function getParameters();
}
