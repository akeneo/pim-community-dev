<?php

namespace Akeneo\Pim\Enrichment\Bundle\Extension\Action;

use Oro\Bundle\DataGridBundle\Extension\Action\Actions\NavigateAction;

/**
 * Grid action for editing entities in a modal
 *
 * @author    Julien Sanchez <julien@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditInModalAction extends NavigateAction
{
    protected $requiredOptions = ['propertyCode', 'fetcher'];
}
