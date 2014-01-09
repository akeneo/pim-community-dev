<?php

namespace Pim\Bundle\GridBundle\Action;

use Oro\Bundle\GridBundle\Action\ActionInterface as OroActionInterface;

/**
 * {@inheritdoc}
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ActionInterface extends OroActionInterface
{
    /**
     * @staticvar string
     */
    const TYPE_PRODUCT_DELETE = 'pim_grid_action_product_delete';
}
