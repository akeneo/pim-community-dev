<?php

namespace Akeneo\Platform\Bundle\DashboardBundle\Widget;

/**
 * A widget view that will be used in the dashboard
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WidgetInterface
{
    /**
     * Get the alias of the widget
     *
     * @return string
     */
    public function getAlias();

    /**
     * Get the template reference
     *
     * ie.: bundle:module:template.format.engine
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

    /**
     * Get the widget data
     *
     * @return mixed
     */
    public function getData();
}
