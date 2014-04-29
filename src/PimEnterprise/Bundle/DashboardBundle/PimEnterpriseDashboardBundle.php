<?php

namespace PimEnterprise\Bundle\DashboardBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Dashboard bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimEnterpriseDashboardBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'PimDashboardBundle';
    }
}
