<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Event;

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
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     */
    const PRE_BUILD = 'pim_rule_engine.rule.pre_build';

    /**
     * This event is thrown after executing rule building.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     */
    const POST_BUILD = 'pim_rule_engine.rule.post_build';

    /**
     * This event is thrown before executing rule selection.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     */
    const PRE_SELECT = 'pim_rule_engine.rule.pre_select';

    /**
     * This event is thrown when an item is skipped.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent instance.
     */
    const SKIP = 'pim_rule_engine.rule.skip';

    /**
     * This event is thrown after executing rule selection.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent instance.
     */
    const POST_SELECT = 'pim_rule_engine.rule.post_select';

    /**
     * This event is thrown before executing rule application.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent instance.
     */
    const PRE_APPLY = 'pim_rule_engine.rule.pre_apply';

    /**
     * This event is thrown after executing rule application.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent instance.
     */
    const POST_APPLY = 'pim_rule_engine.rule.post_apply';

    /**
     * This event is thrown before removing a rule.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     */
    const PRE_REMOVE = 'pim_rule_engine.rule.pre_remove';

    /**
     * This event is thrown after removing a rule.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     */
    const POST_REMOVE = 'pim_rule_engine.rule.post_remove';

    /**
     * This event is thrown before saving a rule.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     */
    const PRE_SAVE = 'pim_rule_engine.rule.pre_save';

    /**
     * This event is thrown after saving a rule.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent instance.
     */
    const POST_SAVE = 'pim_rule_engine.rule.post_save';

    /**
     * This event is thrown before saving many rules.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\BulkRuleEvent instance.
     */
    const PRE_SAVE_ALL = 'pim_rule_engine.rule.pre_save_all';

    /**
     * This event is thrown before a set of subjects is saved
     *
     * The event listener receives a
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     */
    const PRE_SAVE_SUBJECTS = 'pim_rule_engine.pre_save_subjects';

    /**
     * This event is thrown after a set of subjects is saved
     *
     * The event listener receives a
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     */
    const POST_SAVE_SUBJECTS = 'pim_rule_engine.post_save_subjects';

    /**
     * This event is thrown after saving many rules.
     *
     * The event listener receives an
     * Akeneo\Tool\Bundle\RuleEngineBundle\Event\BulkRuleEvent instance.
     */
    const POST_SAVE_ALL = 'pim_rule_engine.rule.post_save_all';

    /**
     * This event is thrown before a rule is executed.
     *
     * The event listener receives a
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     */
    const PRE_EXECUTE = 'pim_rule_engine.rule.pre_execute';

    /**
     * This event is thrown after a rule is executed.
     *
     * The event listener receives a
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     */
    const POST_EXECUTE = 'pim_rule_engine.rule.post_execute';

    /**
     * This event is thrown before a set of rules is executed.
     *
     * The event listener receives a
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     */
    const PRE_EXECUTE_ALL = 'pim_rule_engine.rule.pre_execute_all';

    /**
     * This event is thrown after a set of rules is executed.
     *
     * The event listener receives a
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     */
    const POST_EXECUTE_ALL = 'pim_rule_engine.rule.post_execute_all';
}
