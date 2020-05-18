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

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductRuleExecutionSubscriber implements EventSubscriberInterface
{
    /** @var StepExecution */
    private $stepExecution;

    public function __construct(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public static function getSubscribedEvents()
    {
        return [
            RuleEvents::PRE_APPLY => 'preApply',
            RuleEvents::POST_APPLY => 'postApply',
            RuleEvents::SKIP => 'skip',
        ];
    }

    public function preApply(SelectedRuleEvent $event): void
    {
        $this->stepExecution->incrementSummaryInfo('read_rules');

        $subjectSet = $event->getSubjectSet();
        $this->stepExecution->incrementSummaryInfo('selected_entities', $subjectSet->getSubjectsCursor()->count());
    }

    public function postApply(SelectedRuleEvent $event): void
    {
        $this->stepExecution->incrementSummaryInfo('executed_rules');
    }

    public function skip(SkippedSubjectRuleEvent $event)
    {
        if (null === $this->stepExecution) {
            return;
        }
        $rule = $event->getDefinition();
        $subject = $event->getSubject();
        if ($subject instanceof ProductModelInterface) {
            $identifier = sprintf('product model %s', $subject->getCode());
        } else {
            $identifier = sprintf('product %s', $subject->getIdentifier());
        }

        $message = \sprintf(
            'Rule %s: skipped %s:%s%s',
            $rule->getCode(),
            $identifier,
            PHP_EOL,
            implode(PHP_EOL, $event->getReasons())
        );
        $this->stepExecution->addWarning($message, [], new DataInvalidItem($subject));
        $this->stepExecution->incrementSummaryInfo('skipped_invalid');
    }
}
