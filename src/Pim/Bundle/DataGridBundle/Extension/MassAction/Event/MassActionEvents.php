<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Event;

/**
 * Mass actions events
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MassActionEvents
{
    /**
     * This event is thrown each time a mass action handler is called
     *
     * The event listener receives an
     * Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent instance
     *
     * @staticvar string
     */
    const MASS_ACTION_POST_HANDLER = 'pim_datagrid.extension.mass_action.post_handler';
}
