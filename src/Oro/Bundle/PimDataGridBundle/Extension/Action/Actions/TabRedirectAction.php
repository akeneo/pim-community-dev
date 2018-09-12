<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Action\Actions;

use Oro\Bundle\DataGridBundle\Extension\Action\Actions\AbstractAction;

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
     * @var string[]
     */
    protected $requiredOptions = ['link', 'tab'];
}
