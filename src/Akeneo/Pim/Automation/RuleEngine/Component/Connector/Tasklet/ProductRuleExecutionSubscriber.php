<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet;

use Akeneo\Pim\Automation\RuleEngine\Component\Event\SkippedActionForSubjectEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SavedSubjectsEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductRuleExecutionSubscriber implements EventSubscriberInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var RuleDefinitionInterface */
    private $currentRule;

    public function __construct(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public static function getSubscribedEvents()
    {
        return [
            RuleEvents::PRE_EXECUTE => 'preExecute',
            RuleEvents::POST_SELECT => 'postSelect',
            RuleEvents::POST_EXECUTE => 'postExecute',
            RuleEvents::POST_SAVE_SUBJECTS => 'postSave',
            RuleEvents::SKIP => 'skipInvalid',
            SkippedActionForSubjectEvent::class => 'skipAction',
        ];
    }

    public function preExecute(GenericEvent $event): void
    {
        $this->stepExecution->incrementSummaryInfo('read_rules');
        $this->currentRule = $event->getSubject();
    }

    public function postSelect(SelectedRuleEvent $event): void
    {
        $subjectSet = $event->getSubjectSet();
        $this->stepExecution->incrementSummaryInfo('selected_entities', $subjectSet->getSubjectsCursor()->count());
    }

    public function postExecute(GenericEvent $event): void
    {
        $this->stepExecution->incrementSummaryInfo('executed_rules');
    }

    public function postSave(SavedSubjectsEvent $event): void
    {
        $this->stepExecution->incrementSummaryInfo('updated_entities', count($event->getSubjects()));
    }

    public function skipAction(SkippedActionForSubjectEvent $event): void
    {
        $message = \sprintf(
            'Rule "%s": Could not apply "%s" action to %s: %s',
            $this->currentRule->getCode(),
            $event->getAction()->getType(),
            $this->getEntityIdentifier($event->getSubject()),
            $event->getReason()
        );

        $this->stepExecution->addWarning(
            $message,
            [],
            new DataInvalidItem($event->getSubject())
        );
        $this->stepExecution->incrementSummaryInfo('skipped_invalid');
    }

    public function skipInvalid(SkippedSubjectRuleEvent $event)
    {
        $rule = $event->getDefinition();
        $subject = $event->getSubject();
        if ($subject instanceof ProductModelInterface) {
            $identifier = \sprintf('"%s" product model', $subject->getCode());
        } else {
            $identifier = \sprintf('"%s" product', $subject->getIdentifier());
        }

        $message = \sprintf(
            'Rule "%s": validation failed for %s:%s%s',
            $rule->getCode(),
            $identifier,
            PHP_EOL,
            implode(PHP_EOL, $event->getReasons())
        );
        $this->stepExecution->addWarning($message, [], new DataInvalidItem($subject));
        $this->stepExecution->incrementSummaryInfo('skipped_invalid');
    }

    private function getEntityIdentifier(EntityWithValuesInterface $entity): string
    {
        return \sprintf(
            '%s "%s"',
            $entity instanceof ProductModelInterface ? 'product model' : 'product',
            $entity->getIdentifier(),
        );
    }
}
