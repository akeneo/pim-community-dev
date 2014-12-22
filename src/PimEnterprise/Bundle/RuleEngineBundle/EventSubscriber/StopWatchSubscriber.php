<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\EventSubscriber;

use Symfony\Component\Stopwatch\StopWatch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;

/**
 * Log execution time on rules
 *
 * TODO: not used anymore ?
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class StopWatchSubscriber implements EventSubscriberInterface
{
    /** @var StopWatch */
    protected $stopWatch;

    /** @staticvar string */
    const NAME_PATTERN = 'Rule event : %s %s';

    /**
     * @param StopWatch $stopWatch
     */
    public function __construct(StopWatch $stopWatch = null)
    {
        $this->stopWatch = $stopWatch;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            RuleEvents::PRE_LOAD    => 'preLoad',
            RuleEvents::POST_LOAD   => 'postLoad',
            RuleEvents::PRE_SELECT  => 'preSelect',
            RuleEvents::POST_SELECT => 'postSelect',
            RuleEvents::PRE_APPLY   => 'preApply',
            RuleEvents::POST_APPLY  => 'postApply',
            RuleEvents::PRE_REMOVE  => 'preRemove',
            RuleEvents::POST_REMOVE => 'postRemove',
            RuleEvents::PRE_SAVE    => 'preSave',
            RuleEvents::POST_SAVE   => 'postSave',
        ];
    }

    /**
     * Track preLoad events
     *
     * @param RuleEvent $event
     */
    public function preLoad(RuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->start(
                sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'load'),
                'rule_loading'
            );
        }
    }

    /**
     * Track postLoad events
     *
     * @param RuleEvent $event
     */
    public function postLoad(RuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->stop(sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'load'));
        }
    }

    /**
     * Track preSelect events
     *
     * @param RuleEvent $event
     */
    public function preSelect(RuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->start(
                sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'select'),
                'rule_selecting'
            );
        }
    }

    /**
     * Track postSelect events
     *
     * @param SelectedRuleEvent $event
     */
    public function postSelect(SelectedRuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->stop(sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'select'));
        }
    }

    /**
     * Track preApply events
     *
     * @param SelectedRuleEvent $event
     */
    public function preApply(SelectedRuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->start(
                sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'apply'),
                'rule_applying'
            );
        }
    }

    /**
     * Track postApply events
     *
     * @param SelectedRuleEvent $event
     */
    public function postApply(SelectedRuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->stop(sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'apply'));
        }
    }

    /**
     * Track preRemove events
     *
     * @param RuleEvent $event
     */
    public function preRemove(RuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->start(
                sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'remove'),
                'rule_removing'
            );
        }
    }

    /**
     * Track postRemove events
     *
     * @param RuleEvent $event
     */
    public function postRemove(RuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->stop(sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'remove'));
        }
    }

    /**
     * Track preSave events
     *
     * @param RuleEvent $event
     */
    public function preSave(RuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->start(
                sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'save'),
                'rule_saving'
            );
        }
    }

    /**
     * Track postSave events
     *
     * @param RuleEvent $event
     */
    public function postSave(RuleEvent $event)
    {
        if ($this->stopWatch) {
            $this->stopWatch->stop(sprintf(static::NAME_PATTERN, $event->getDefinition()->getCode(), 'save'));
        }
    }
}
