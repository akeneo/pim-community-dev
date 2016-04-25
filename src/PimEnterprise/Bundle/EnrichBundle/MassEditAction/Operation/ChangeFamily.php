<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ChangeFamily as BaseChangeFamily;

/**
 * Batch operation to change family of products
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ChangeFamily extends BaseChangeFamily
{
    /**
     * {@inheritdoc}
     *
     * We override the parent job to apply rules in further step
     */
    public function getBatchJobCode()
    {
        return 'update_product_value_with_permission_and_rules';
    }
}
