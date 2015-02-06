<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Event;

/**
 * Rule events
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
final class RuleEvents
{
    /**
     * This event is thrown before executing rule building.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_BUILD = 'pim_rule_engine.rule.pre_build';

    /**
     * This event is thrown after executing rule building.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const POST_BUILD = 'pim_rule_engine.rule.post_build';

    /**
     * This event is thrown before executing rule selection.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_SELECT = 'pim_rule_engine.rule.pre_select';

    /**
     * This event is thrown when an item is skipped.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent instance.
     *
     * @staticvar string
     */
    const SKIP = 'pim_rule_engine.rule.skip';

    /**
     * This event is thrown after executing rule selection.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\SelectedRuleEvent instance.
     *
     * @staticvar string
     */
    const POST_SELECT = 'pim_rule_engine.rule.post_select';

    /**
     * This event is thrown before executing rule application.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\SelectedRuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_APPLY = 'pim_rule_engine.rule.pre_apply';

    /**
     * This event is thrown after executing rule application.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\SelectedRuleEvent instance.
     *
     * @staticvar string
     */
    const POST_APPLY = 'pim_rule_engine.rule.post_apply';

    /**
     * This event is thrown before removing a rule.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'pim_rule_engine.rule.pre_remove';

    /**
     * This event is thrown after removing a rule.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const POST_REMOVE = 'pim_rule_engine.rule.post_remove';

    /**
     * This event is thrown before saving a rule.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_SAVE = 'pim_rule_engine.rule.pre_save';

    /**
     * This event is thrown after saving a rule.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     *
     * @staticvar string
     */
    const POST_SAVE = 'pim_rule_engine.rule.post_save';

    /**
     * This event is thrown before saving many rules.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\BulkRuleEvent instance.
     *
     * @staticvar string
     */
    const PRE_SAVE_ALL = 'pim_rule_engine.rule.pre_save_all';

    /**
     * This event is thrown after saving many rules.
     *
     * The event listener receives an
     * Akeneo\Bundle\RuleEngineBundle\Event\BulkRuleEvent instance.
     *
     * @staticvar string
     */
    const POST_SAVE_ALL = 'pim_rule_engine.rule.post_save_all';
}
