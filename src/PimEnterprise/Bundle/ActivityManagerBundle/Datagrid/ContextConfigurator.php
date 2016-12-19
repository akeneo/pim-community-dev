<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Datagrid;

use PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator
    as EnterpriseContextConfigurator;

/**
 * Override of the Enterprise ContextConfigurator of the datagrid.
 *
 * This override is only to remove the check on permissions for the granted attributes seen by the user.
 * This way we can filter the datagrid with filters the user doesn't have read access on, used by projects.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ContextConfigurator extends EnterpriseContextConfigurator
{
    /**
     * {@inheritdoc}
     */
    protected function getAttributeIdsUseableInGrid($attributeCodes = null)
    {
        return $this->attributeRepository->getAttributeIdsUseableInGrid($attributeCodes);
    }
}
