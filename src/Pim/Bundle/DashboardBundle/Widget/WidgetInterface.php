<?php

namespace Pim\Bundle\DashboardBundle\Widget;

/**
 * 
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WidgetInterface
{
    public function getTemplate();
    public function getParameters();
}
