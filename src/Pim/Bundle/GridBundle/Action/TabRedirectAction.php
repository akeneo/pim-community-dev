<?php

namespace Pim\Bundle\GridBundle\Action;

use Oro\Bundle\GridBundle\Action\AbstractAction;

/**
 * Grid action for redirection to a specific form tab
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TabRedirectAction extends AbstractAction
{
    /**
     * @var string
     */
    protected $type = ActionInterface::TYPE_TAB_REDIRECT;

    /**
     * @var string[]
     */
    protected $requiredOptions = array('link', 'tab');
}
