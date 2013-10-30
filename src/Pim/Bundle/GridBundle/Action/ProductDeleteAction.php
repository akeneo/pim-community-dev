<?php

namespace Pim\Bundle\GridBundle\Action;

use Oro\Bundle\GridBundle\Action\DeleteAction;

/**
 * Extends delete action to treat special case of products
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDeleteAction extends DeleteAction
{
    /**
     * @var string
     */
    protected $type = ActionInterface::TYPE_PRODUCT_DELETE;
}
