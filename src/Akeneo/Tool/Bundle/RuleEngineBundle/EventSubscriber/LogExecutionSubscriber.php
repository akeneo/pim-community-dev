<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Log rules execution
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LogExecutionSubscriber implements EventSubscriberInterface
{
    protected LoggerInterface $logger;
    const NAME_PATTERN = 'Rule "%s", event "%s"';
    const LOGGING_LEVEL_INFO = 'info';
    const LOGGING_LEVEL_DEBUG = 'debug';
    private string $loggingLevel;

    public function __construct(LoggerInterface $logger, string $loggingLevel)
    {
        $this->logger = $logger;
        $this->loggingLevel = $loggingLevel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RuleEvents::PRE_BUILD => 'preBuild',
            RuleEvents::POST_BUILD => 'postBuild',
            RuleEvents::PRE_SELECT => 'preSelect',
            RuleEvents::SKIP => 'skip',
            RuleEvents::POST_SELECT => 'postSelect',
            RuleEvents::PRE_APPLY => 'preApply',
            RuleEvents::POST_APPLY => 'postApply',
        ];
    }

    /**
     * Track preBuild events
     */
    public function preBuild(RuleEvent $event): void
    {
        if (!$this->isInfoOrLowerSeverityLogger()) {
            return;
        }
        $ruleDefinition = $event->getDefinition();
        $message = sprintf(static::NAME_PATTERN, $ruleDefinition->getCode(), RuleEvents::PRE_BUILD);
        $this->logger->info($message);
    }

    /**
     * Track postBuild events
     */
    public function postBuild(RuleEvent $event): void
    {
        if (!$this->isInfoOrLowerSeverityLogger()) {
            return;
        }
        $ruleDefinition = $event->getDefinition();
        $message = sprintf(static::NAME_PATTERN, $ruleDefinition->getCode(), RuleEvents::POST_BUILD);
        $this->logger->info($message);
    }

    /**
     * Track preSelect events
     */
    public function preSelect(RuleEvent $event): void
    {
        if (!$this->isInfoOrLowerSeverityLogger()) {
            return;
        }
        $ruleDefinition = $event->getDefinition();
        $message = sprintf(static::NAME_PATTERN, $ruleDefinition->getCode(), RuleEvents::PRE_SELECT);
        $this->logger->info($message);
    }

    /**
     * Track skipped events
     */
    public function skip(SkippedSubjectRuleEvent $event): void
    {
        $skippedReasons = implode(', ', $event->getReasons());
        $patternItem = static::NAME_PATTERN.': subject "%s" has been skipped due to "%s".';
        $subject = $event->getSubject();
        $messageItem = sprintf(
            $patternItem,
            $event->getDefinition()->getCode(),
            RuleEvents::SKIP,
            $subject instanceof ProductInterface ? $subject->getUuid()->toString() : $subject->getId(),
            $skippedReasons
        );
        $this->logger->warning($messageItem);
    }

    /**
     * Track postSelect events
     */
    public function postSelect(SelectedRuleEvent $event): void
    {
        if (!$this->isInfoOrLowerSeverityLogger()) {
            return;
        }
        $ruleDefinition = $event->getDefinition();
        $subjectSet = $event->getSubjectSet();
        $pattern = static::NAME_PATTERN.': %s items selected.';
        $message = sprintf(
            $pattern,
            $ruleDefinition->getCode(),
            RuleEvents::POST_SELECT,
            count($subjectSet->getSubjectsCursor())
        );
        $this->logger->info($message);
    }

    /**
     * Track preApply events
     */
    public function preApply(SelectedRuleEvent $event): void
    {
        if (!$this->isInfoOrLowerSeverityLogger()) {
            return;
        }
        $ruleDefinition = $event->getDefinition();
        $subjectSet = $event->getSubjectSet();
        $pattern = static::NAME_PATTERN.': %s items to update.';
        $message = sprintf(
            $pattern,
            $ruleDefinition->getCode(),
            RuleEvents::PRE_APPLY,
            count($subjectSet->getSubjectsCursor())
        );
        $this->logger->info($message);
    }

    /**
     * Track postApply events
     */
    public function postApply(SelectedRuleEvent $event): void
    {
        if (!$this->isInfoOrLowerSeverityLogger()) {
            return;
        }
        $ruleDefinition = $event->getDefinition();
        $subjectSet = $event->getSubjectSet();
        $pattern = static::NAME_PATTERN.': %s items updated.';
        $message = sprintf(
            $pattern,
            $ruleDefinition->getCode(),
            RuleEvents::POST_APPLY,
            count($subjectSet->getSubjectsCursor())
        );
        $this->logger->info($message);
    }

    private function isInfoOrLowerSeverityLogger(): bool
    {
        return in_array(strtolower($this->loggingLevel), [
            self::LOGGING_LEVEL_INFO,
            self::LOGGING_LEVEL_DEBUG,
        ]);
    }
}
