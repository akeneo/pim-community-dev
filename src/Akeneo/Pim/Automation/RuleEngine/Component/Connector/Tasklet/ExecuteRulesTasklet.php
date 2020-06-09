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

use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ExecuteRulesTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var RunnerInterface */
    private $ruleRunner;

    /** @var DryRunnerInterface */
    private $dryRuleRunner;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        DryRunnerInterface $dryRuleRunner,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleRunner = $ruleRunner;
        $this->dryRuleRunner = $dryRuleRunner;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        $dryRun = $this->stepExecution->getJobParameters()->get('dry_run');
        $this->stepExecution->setSummary(
            [
                'read_rules' => 0,
                'selected_entities' => 0,
                'skipped_invalid' => 0,
                'updated_entities' => 0,
                'executed_rules' => 0,
            ]
        );

        $subscriber = new ProductRuleExecutionSubscriber($this->stepExecution);
        $this->eventDispatcher->addSubscriber($subscriber);

        foreach ($this->getRuleDefinitions() as $ruleDefinition) {
            try {
                if (true === $dryRun) {
                    $this->dryRuleRunner->dryRun($ruleDefinition);
                } else {
                    $this->ruleRunner->run($ruleDefinition);
                }
            } catch (\LogicException $e) {
                $error = \sprintf('Rule "%s": %s', $ruleDefinition->getCode(), $e->getMessage());
                $this->stepExecution->addError($error);
                $this->stepExecution->incrementSummaryInfo('errored_rules');
                if ($this->stepExecution->getJobParameters()->get('stop_on_error')) {
                    throw $e;
                }
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
