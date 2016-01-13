<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\EditCommonAttributes as BaseEditCommonAttributes;

/**
 * Edit common attributes of given products
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class EditCommonAttributes extends BaseEditCommonAttributes
{
    /**
     * {@inheritdoc}
     */
    public function getBatchJobCode()
    {
        return 'edit_common_attributes_with_permission';
    }
}
