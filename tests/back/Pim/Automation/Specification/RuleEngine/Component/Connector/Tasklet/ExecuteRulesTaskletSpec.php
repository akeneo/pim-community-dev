<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet;

use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet\ExecuteRulesTasklet;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExecuteRulesTaskletSpec extends ObjectBehavior
{
    function let(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        DryRunnerInterface $dryRuleRunner,
        EventDispatcherInterface $eventDispatcher,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        JobStopper $jobStopper
    ) {
        $this->beConstructedWith($ruleDefinitionRepository, $ruleRunner, $dryRuleRunner, $eventDispatcher, $jobStopper);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldHaveType(ExecuteRulesTasklet::class);
    }

    function it_executes_given_rules(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        JobStopper $jobStopper
    ) {
        $jobParameters->get('rule_codes')->willReturn(['rule1', 'rule2']);
        $jobParameters->get('dry_run')->willReturn(false);

        $ruleDefinitionRepository->findBy(['code' => ['rule1', 'rule2']], ['priority' => 'DESC'])
            ->willReturn([new RuleDefinition(), new RuleDefinition()]);

        $stepExecution->setSummary(Argument::type('array'))->shouldBeCalled();
        $ruleRunner->run(Argument::type(RuleDefinition::class))->shouldBeCalledTimes(2);
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_executes_all_the_rules(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        JobStopper $jobStopper
    ) {
        $jobParameters->get('rule_codes')->willReturn([]);
        $jobParameters->get('dry_run')->willReturn(false);

        $ruleDefinitionRepository->findEnabledOrderedByPriority()
            ->willReturn([new RuleDefinition(), new RuleDefinition(), new RuleDefinition()]);

        $stepExecution->setSummary(Argument::type('array'))->shouldBeCalled();
        $ruleRunner->run(Argument::type(RuleDefinition::class))->shouldBeCalledTimes(3);
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_dry_runs_a_rule(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        DryRunnerInterface $dryRuleRunner,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        JobStopper $jobStopper
    ) {
        $jobParameters->get('rule_codes')->willReturn(['rule1']);
        $jobParameters->get('dry_run')->willReturn(true);

        $ruleDefinitionRepository->findBy(['code' => ['rule1']], ['priority' => 'DESC'])
            ->willReturn([new RuleDefinition(), new RuleDefinition()]);

        $stepExecution->setSummary(Argument::type('array'))->shouldBeCalled();
        $dryRuleRunner->dryRun(Argument::type(RuleDefinition::class))->shouldBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_stops_rules_execution_when_an_error_occurs_in_strict_mode(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        RuleDefinition $ruleDefinition1,
        RuleDefinition $ruleDefinition2,
        JobStopper $jobStopper
    ) {
        $ruleDefinition1->getCode()->willReturn('my_rule_code');
        $ruleDefinition1->getContent()->willReturn(['normalized rule content']);
        $ruleDefinitionRepository->findEnabledOrderedByPriority()->willReturn([$ruleDefinition1, $ruleDefinition2]);
        $jobParameters->get('rule_codes')->willReturn([]);
        $jobParameters->get('dry_run')->willReturn(false);
        $jobParameters->get('stop_on_error')->willReturn(true);

        $stepExecution->setSummary(Argument::type('array'))->shouldBeCalled();

        $exception = new \LogicException('error message');
        $ruleRunner->run($ruleDefinition1)->willThrow($exception);

        $stepExecution->addWarning(
            'The "{{ ruleCode }}" rule could not be executed: {{ error }}',
            [
                '{{ ruleCode }}' => 'my_rule_code',
                '{{ error }}' => 'error message',
            ],
            new DataInvalidItem(
                [
                    'code' => 'my_rule_code',
                    'content' => ['normalized rule content'],
                ]
            )
        )->shouldBeCalled();

        $stepExecution->addError('Rule "my_rule_code": error message')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('errored_rules')->shouldBeCalled();

        $ruleRunner->run($ruleDefinition2)->shouldNotBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->shouldThrow($exception)->during('execute');
    }

    function it_keeps_running_rules_execution_when_an_error_occurs_in_non_strict_mode(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        RuleDefinition $ruleDefinition1,
        RuleDefinition $ruleDefinition2,
        JobStopper $jobStopper
    ) {
        $ruleDefinition1->getCode()->willReturn('my_rule_code');
        $ruleDefinition1->getContent()->willReturn(['normalized rule content']);
        $ruleDefinitionRepository->findEnabledOrderedByPriority()->willReturn([$ruleDefinition1, $ruleDefinition2]);
        $jobParameters->get('rule_codes')->willReturn([]);
        $jobParameters->get('dry_run')->willReturn(false);
        $jobParameters->get('stop_on_error')->willReturn(false);

        $stepExecution->setSummary(Argument::type('array'))->shouldBeCalled();

        $exception = new \LogicException('error message');
        $ruleRunner->run($ruleDefinition1)->willThrow($exception);

        $stepExecution->addWarning(
            'The "{{ ruleCode }}" rule could not be executed: {{ error }}',
            [
                '{{ ruleCode }}' => 'my_rule_code',
                '{{ error }}' => 'error message',
            ],
            new DataInvalidItem(
                [
                    'code' => 'my_rule_code',
                    'content' => ['normalized rule content'],
                ]
            )
        )->shouldBeCalled();

        $stepExecution->addError('Rule "my_rule_code": error message')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('errored_rules')->shouldBeCalled();

        $ruleRunner->run($ruleDefinition2)->shouldBeCalled();
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }
}
