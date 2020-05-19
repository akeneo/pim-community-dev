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

use Akeneo\Pim\Automation\RuleEngine\Component\Runner\ProductRuleRunner;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ExecuteRulesTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var ProductRuleRunner */
    private $ruleRunner;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        ProductRuleRunner $ruleRunner,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleRunner = $ruleRunner;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        $subscriber = new ProductRuleExecutionSubscriber($this->stepExecution);
        $this->eventDispatcher->addSubscriber($subscriber);

        foreach ($this->getRuleDefinitions() as $ruleDefinition) {
            try {
                $this->ruleRunner->run($ruleDefinition);
            } catch (\LogicException $e) {
                $this->stepExecution->addError($e->getMessage());
                $this->stepExecution->incrementSummaryInfo('errored_rules');
            }
        }
        $this->eventDispatcher->removeSubscriber($subscriber);
    }

    private function getRuleDefinitions(): iterable
    {
        $ruleCodes = $this->stepExecution->getJobParameters()->get('rule_codes');
        if (!empty($ruleCodes)) {
            $ruleDefinitions = $this->ruleDefinitionRepository->findBy(['code' => $ruleCodes], ['priority' => 'DESC']);
        } else {
            $ruleDefinitions = $this->ruleDefinitionRepository->findAllOrderedByPriority();
        }
        foreach ($ruleDefinitions as $ruleDefinition) {
            yield $ruleDefinition;
        }
    }
}
