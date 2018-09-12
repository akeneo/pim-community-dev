<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event;

/**
 * Mass actions events
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @final
 */
final class MassActionEvents
{
    /**
     * These event are thrown when mass action handlers are called
     *
     * The event listener receives an
     * Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent instance
     *
     * @staticvar string
     */
    const MASS_DELETE_POST_HANDLER = 'pim_datagrid.extension.mass_action.delete.post_handler';
    const MASS_DELETE_PRE_HANDLER = 'pim_datagrid.extension.mass_action.delete.pre_handler';
    const MASS_EDIT_POST_HANDLER = 'pim_datagrid.extension.mass_action.edit.post_handler';
    const MASS_EDIT_PRE_HANDLER = 'pim_datagrid.extension.mass_action.edit.pre_handler';
    const MASS_EXPORT_POST_HANDLER = 'pim_datagrid.extension.mass_action.export.post_handler';
    const MASS_EXPORT_PRE_HANDLER = 'pim_datagrid.extension.mass_action.export.pre_handler';
    const SEQUENTIAL_EDIT_POST_HANDLER = 'pim_datagrid.extension.mass_action.sequential_edit.post_handler';
    const SEQUENTIAL_EDIT_PRE_HANDLER = 'pim_datagrid.extension.mass_action.sequential_edit.pre_handler';
}
