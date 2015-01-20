<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\EventSubscriber;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Log rules execution
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LogExecutionSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @staticvar string */
    const NAME_PATTERN = 'Rule "%s", event "%s"';

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            RuleEvents::PRE_BUILD   => 'preBuild',
            RuleEvents::POST_BUILD  => 'postBuild',
            RuleEvents::PRE_SELECT  => 'preSelect',
            RuleEvents::POST_SELECT => 'postSelect',
            RuleEvents::PRE_APPLY   => 'preApply',
            RuleEvents::POST_APPLY  => 'postApply'
        ];
    }

    /**
     * Track preBuild events
     *
     * @param RuleEvent $event
     */
    public function preBuild(RuleEvent $event)
    {
        $ruleDefinition = $event->getDefinition();
        $message = sprintf(static::NAME_PATTERN, $ruleDefinition->getCode(), RuleEvents::PRE_BUILD);
        $this->logger->info($message);
    }

    /**
     * Track postBuild events
     *
     * @param RuleEvent $event
     */
    public function postBuild(RuleEvent $event)
    {
        $ruleDefinition = $event->getDefinition();
        $message = sprintf(static::NAME_PATTERN, $ruleDefinition->getCode(), RuleEvents::POST_BUILD);
        $this->logger->info($message);
    }

    /**
     * Track preSelect events
     *
     * @param RuleEvent $event
     */
    public function preSelect(RuleEvent $event)
    {
        $ruleDefinition = $event->getDefinition();
        $message = sprintf(static::NAME_PATTERN, $ruleDefinition->getCode(), RuleEvents::PRE_SELECT);
        $this->logger->info($message);
    }

    /**
     * Track postSelect events
     *
     * @param SelectedRuleEvent $event
     */
    public function postSelect(SelectedRuleEvent $event)
    {
        $ruleDefinition = $event->getDefinition();
        $subjectSet = $event->getSubjectSet();
        $pattern = static::NAME_PATTERN.': %s items selected.';
        $message = sprintf(
            $pattern,
            $ruleDefinition->getCode(),
            RuleEvents::POST_SELECT,
            count($subjectSet->getSubjects())
        );
        $this->logger->info($message);
    }

    /**
     * Track preApply events
     *
     * @param SelectedRuleEvent $event
     */
    public function preApply(SelectedRuleEvent $event)
    {
        $ruleDefinition = $event->getDefinition();
        $subjectSet = $event->getSubjectSet();
        $pattern = static::NAME_PATTERN.': %s items to update.';
        $message = sprintf(
            $pattern,
            $ruleDefinition->getCode(),
            RuleEvents::PRE_APPLY,
            count($subjectSet->getSubjects())
        );
        $this->logger->info($message);
    }

    /**
     * Track postApply events
     *
     * @param SelectedRuleEvent $event
     */
    public function postApply(SelectedRuleEvent $event)
    {
        $ruleDefinition = $event->getDefinition();
        $subjectSet = $event->getSubjectSet();
        $pattern = static::NAME_PATTERN.': %s items updated.';
        $message = sprintf(
            $pattern,
            $ruleDefinition->getCode(),
            RuleEvents::POST_APPLY,
            count($subjectSet->getSubjects())
        );
        $this->logger->info($message);

        $skippedSubjects = $subjectSet->getSkippedSubjects();
        if (count($skippedSubjects) > 0) {
            $this->logSkippedSubjects($ruleDefinition->getCode(), $skippedSubjects);
        }
    }

    /**
     * Log skipped subjects with reasons
     *
     * @param string $ruleCode
     * @param array  $skippedSubjects
     */
    protected function logSkippedSubjects($ruleCode, $skippedSubjects)
    {
        if (count($skippedSubjects) > 0) {
            $pattern = static::NAME_PATTERN . ': %s subjects skipped.';
            $message = sprintf(
                $pattern,
                $ruleCode,
                RuleEvents::POST_APPLY,
                count($skippedSubjects)
            );
            $this->logger->warning($message);

            $patternItem = static::NAME_PATTERN . ': subject "%s" has been skipped due to "%s".';
            foreach ($skippedSubjects as $skippedItem) {
                $skippedSubject = $skippedItem['subject'];
                $skippedReasons = implode(', ', $skippedItem['reasons']);
                $messageItem = sprintf(
                    $patternItem,
                    $ruleCode,
                    RuleEvents::POST_APPLY,
                    $skippedSubject->getId(),
                    $skippedReasons
                );
                $this->logger->warning($messageItem);
            }
        }
    }
}
