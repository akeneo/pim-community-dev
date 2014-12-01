<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Event;

/**
 * Rule events
 *
 * @author Julien Sanchez <julien@akeneo.com>
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
     * PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent instance.
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

    /**
     * This event is thrown before removing a rule.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'pim_rule_engine.rule.pre_remove';

    /**
     * This event is thrown after removing a rule.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const POST_REMOVE = 'pim_rule_engine.rule.post_remove';

    /**
     * This event is thrown before saving a rule.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_SAVE = 'pim_rule_engine.rule.pre_save';

    /**
     * This event is thrown after saving a rule.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const POST_SAVE = 'pim_rule_engine.rule.post_save';

    /**
     * This event is thrown before updating a rule.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_UPDATE = 'pim_rule_engine.rule.pre_update';

    /**
     * This event is thrown after updating a rule.
     *
     * The event listener receives an
     * PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const POST_UPDATE = 'pim_rule_engine.rule.post_update';
}
