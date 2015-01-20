<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DashboardBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Dashboard bundle
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
