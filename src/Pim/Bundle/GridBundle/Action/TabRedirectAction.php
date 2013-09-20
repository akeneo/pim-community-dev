<?php

namespace Pim\Bundle\GridBundle\Action;

use Oro\Bundle\GridBundle\Action\AbstractAction;

/**
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TabRedirectAction extends AbstractAction implements ActionInterface
{
    /**
     * @var string
     */
    protected $type = self::TYPE_REDIRECT;

    /**
     * @var array
     */
    protected $requiredOptions = array('link', 'tab');
}
