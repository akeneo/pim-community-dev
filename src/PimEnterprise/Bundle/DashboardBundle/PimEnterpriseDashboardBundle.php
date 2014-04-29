<?php

namespace PimEnterprise\Bundle\DashboardBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Dashboard bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
