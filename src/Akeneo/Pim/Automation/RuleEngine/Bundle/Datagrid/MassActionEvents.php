<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid;

/**
 * Mass actions events
 *
 * @author Julien Janvier <j.janvier@gmail.com>
 */
final class MassActionEvents
{
    /**
     * These event are thrown when mass action handlers are called
     *
     * The event listener receives an
     * Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\MassActionEvent instance
     */
    const MASS_RULE_IMPACTED_PRODUCT_COUNT_PRE_HANDLER = 'pim_datagrid.extension.mass_action.rule_impacted_product_count.pre_handler';
    const MASS_RULE_IMPACTED_PRODUCT_COUNT_POST_HANDLER = 'pim_datagrid.extension.mass_action.rule_impacted_product_count.post_handler';
}
