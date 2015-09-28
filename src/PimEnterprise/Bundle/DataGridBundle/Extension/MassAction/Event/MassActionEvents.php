<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event;

/**
 * Mass actions events
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 *
 * @final
 */
final class MassActionEvents
{
    /**
     * These event are thrown when mass action handlers are called
     *
     * The event listener receives an
     * PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent instance
     *
     * @staticvar string
     */
    const MASS_APPROVE_POST_HANDLER = 'pim_datagrid.extension.mass_action.mass_approve.post_handler';
    const MASS_APPROVE_PRE_HANDLER  = 'pim_datagrid.extension.mass_action.mass_approve.pre_handler';
    const MASS_REFUSE_POST_HANDLER  = 'pim_datagrid.extension.mass_action.mass_refuse.post_handler';
    const MASS_REFUSE_PRE_HANDLER   = 'pim_datagrid.extension.mass_action.mass_refuse.pre_handler';
}
