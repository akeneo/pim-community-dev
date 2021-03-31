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

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ExecuteRulesTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private ?StepExecution $stepExecution = null;
    private RuleDefinitionRepositoryInterface $ruleDefinitionRepository;
    private RunnerInterface $ruleRunner;
    private DryRunnerInterface $dryRuleRunner;
    private EventDispatcherInterface $eventDispatcher;
    private JobStopper $jobStopper;
    private JobRepositoryInterface $jobRepository;
    private EntityManagerClearerInterface $cacheClearer;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        DryRunnerInterface $dryRuleRunner,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleRunner = $ruleRunner;
        $this->dryRuleRunner = $dryRuleRunner;
        $this->eventDispatcher = $eventDispatcher;
        $this->jobRepository = $jobRepository;
        $this->jobStopper = $jobStopper;
        $this->cacheClearer = $cacheClearer;
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        $dryRun = $this->stepExecution->getJobParameters()->get('dry_run');
        $this->stepExecution->setTotalItems($this->getTotalItemImpacted());
        $this->stepExecution->setSummary(
            [
                'read_rules' => 0,
                'selected_entities' => 0,
                'skipped_invalid' => 0,
                'updated_entities' => 0,
                'executed_rules' => 0,
            ]
        );

        $subscriber = new ProductRuleExecutionSubscriber($this->stepExecution, $this->jobRepository);
        $this->eventDispatcher->addSubscriber($subscriber);

        foreach ($this->getRuleDefinitions() as $ruleDefinition) {
            if ($this->jobStopper->isStopping($this->stepExecution)) {
                $this->jobStopper->stop($this->stepExecution);
                break;
            }

            try {
                if (true === $dryRun) {
                    $this->dryRuleRunner->dryRun($ruleDefinition);
                } else {
                    $this->ruleRunner->run($ruleDefinition);
                }
            } catch (\LogicException $e) {
                $error = 'The "{{ ruleCode }}" rule could not be executed: {{ error }}';
                $rule = [
                    'code' => $ruleDefinition->getCode(),
                    'content' => $ruleDefinition->getContent(),
                ];
                $this->stepExecution->addWarning(
                    $error,
                    [
                        '{{ ruleCode }}' => $ruleDefinition->getCode(),
                        '{{ error }}' => $e->getMessage(),
                    ],
                    new DataInvalidItem($rule)
                );
                $this->stepExecution->addError(\sprintf('Rule "%s": %s', $ruleDefinition->getCode(), $e->getMessage()));

                $this->stepExecution->incrementSummaryInfo('errored_rules');
                if ($this->stepExecution->getJobParameters()->get('stop_on_error')) {
                    throw $e;
                }
            }
        }
        if ($this->jobStopper->isStopping($this->stepExecution)) {
            $this->jobStopper->stop($this->stepExecution);
        }

        $this->eventDispatcher->removeSubscriber($subscriber);
    }

    private function getRuleDefinitions(): iterable
    {
        $ruleCodes = $this->stepExecution->getJobParameters()->get('rule_codes');
        if (!empty($ruleCodes)) {
            $ruleDefinitions = $this->ruleDefinitionRepository->findBy(['code' => $ruleCodes], ['priority' => 'DESC']);
        } else {
            $ruleDefinitions = $this->ruleDefinitionRepository->findEnabledOrderedByPriority();
        }

        foreach ($ruleDefinitions as $ruleDefinition) {
            yield $ruleDefinition;
        }
    }

    public function isTrackable(): bool
    {
        return true;
    }

    private function getTotalItemImpacted(): int
    {
        $totalProductsImpacted = 0;
        foreach ($this->getRuleDefinitions() as $ruleDefinition) {
            $totalProductsImpacted += $this->countProducts($ruleDefinition);
            $this->cacheClearer->clear();
        }

        return $totalProductsImpacted;
    }

    private function countProducts(RuleDefinition $ruleDefinition)
    {
        $ruleSubjectSet = $this->dryRuleRunner->dryRun($ruleDefinition);
        if (null === $ruleSubjectSet) {
            throw new \RuntimeException(
                sprintf('Impossible to dry run rule definition of id %s', (string) $ruleDefinition->getId())
            );
        }

        return $ruleSubjectSet->getSubjectsCursor()->count();
    }
}
