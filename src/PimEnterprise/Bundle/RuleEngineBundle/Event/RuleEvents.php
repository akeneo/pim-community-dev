<?php

namespace PimEnterprise\Bundle\RuleEngineBundle\Event;

/**
 * Rule events
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RuleEvents
{
    /**
     * This event is thrown before executing rule loading.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_LOAD = 'pim_rule_engine.rule.pre_load';

    /**
     * This event is thrown after executing rule loading.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const POST_LOAD = 'pim_rule_engine.rule.post_load';

    /**
     * This event is thrown before executing rule selection.
     *
     * The event listener receives an
     * Pim\Bundle\CatalogBundle\Event\ProductEvent instance.
     *
     * @staticvar string
     */
    const PRE_SELECT = 'pim_rule_engine.rule.pre_select';

    /**
     * This event is thrown after executing rule selection.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent instance.
     *
     * @staticvar string
     */
    const POST_SELECT = 'pim_rule_engine.rule.post_select';

    /**
     * This event is thrown before executing rule application.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_APPLY = 'pim_rule_engine.rule.pre_apply';

    /**
     * This event is thrown after executing rule application.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent instance.
     *
     * @staticvar string
     */
    const POST_APPLY = 'pim_rule_engine.rule.post_apply';
}
