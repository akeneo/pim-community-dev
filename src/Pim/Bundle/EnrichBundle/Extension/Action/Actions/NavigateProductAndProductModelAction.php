<?php

namespace Pim\Bundle\EnrichBundle\Extension\Action\Actions;

use Oro\Bundle\DataGridBundle\Extension\Action\Actions\NavigateAction;

/**
 * Grid action for redirecting product and product models to their enrichment page.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NavigateProductAndProductModelAction extends NavigateAction
{
    protected $requiredOptions = [];
}
