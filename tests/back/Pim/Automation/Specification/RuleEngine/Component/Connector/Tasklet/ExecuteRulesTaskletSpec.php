<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet;

use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Tasklet\ExecuteRulesTasklet;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSet;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
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
        JobRepositoryInterface $jobRepository,
        JobStopper $jobStopper
    ) {
        $this->beConstructedWith($ruleDefinitionRepository, $ruleRunner, $dryRuleRunner, $eventDispatcher, $jobRepository, $jobStopper);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(TaskletInterface::class);
        $this->shouldHaveType(ExecuteRulesTasklet::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    function it_executes_given_rules(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        DryRunnerInterface $dryRuleRunner,
        RuleSubjectSetInterface $ruleSubjectSet1,
        RuleSubjectSetInterface $ruleSubjectSet2,
        CursorInterface $dryRunCursor1,
        CursorInterface $dryRunCursor2,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        JobStopper $jobStopper
    ) {
        $jobParameters->get('rule_codes')->willReturn(['rule1', 'rule2']);
        $jobParameters->get('dry_run')->willReturn(false);

        $ruleDefinitionRepository->findBy(['code' => ['rule1', 'rule2']], ['priority' => 'DESC'])
            ->willReturn([new RuleDefinition(), new RuleDefinition()]);

        $dryRuleRunner
            ->dryRun(Argument::type(RuleDefinition::class))
            ->shouldBeCalledTimes(2)
            ->willReturn($ruleSubjectSet1, $ruleSubjectSet2);

        $ruleSubjectSet1->getSubjectsCursor()->willReturn($dryRunCursor1);
        $ruleSubjectSet2->getSubjectsCursor()->willReturn($dryRunCursor2);

        $dryRunCursor1->count()->willReturn(1);
        $dryRunCursor2->count()->willReturn(2);

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $stepExecution->setSummary(Argument::type('array'))->shouldBeCalled();
        $ruleRunner->run(Argument::type(RuleDefinition::class))->shouldBeCalledTimes(2);
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_executes_all_the_rules(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        DryRunnerInterface $dryRuleRunner,
        RuleSubjectSetInterface $ruleSubjectSet1,
        RuleSubjectSetInterface $ruleSubjectSet2,
        RuleSubjectSetInterface $ruleSubjectSet3,
        CursorInterface $dryRunCursor1,
        CursorInterface $dryRunCursor2,
        CursorInterface $dryRunCursor3,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        JobStopper $jobStopper
    ) {
        $jobParameters->get('rule_codes')->willReturn([]);
        $jobParameters->get('dry_run')->willReturn(false);

        $ruleDefinitionRepository->findEnabledOrderedByPriority()
            ->willReturn([new RuleDefinition(), new RuleDefinition(), new RuleDefinition()]);

        $dryRuleRunner
            ->dryRun(Argument::type(RuleDefinition::class))
            ->shouldBeCalledTimes(3)
            ->willReturn($ruleSubjectSet1, $ruleSubjectSet2, $ruleSubjectSet3);

        $ruleSubjectSet1->getSubjectsCursor()->willReturn($dryRunCursor1);
        $ruleSubjectSet2->getSubjectsCursor()->willReturn($dryRunCursor2);
        $ruleSubjectSet3->getSubjectsCursor()->willReturn($dryRunCursor3);

        $dryRunCursor1->count()->willReturn(1);
        $dryRunCursor2->count()->willReturn(2);
        $dryRunCursor3->count()->willReturn(3);

        $stepExecution->setTotalItems(6)->shouldBeCalledOnce();
        $stepExecution->setSummary(Argument::type('array'))->shouldBeCalled();
        $ruleRunner->run(Argument::type(RuleDefinition::class))->shouldBeCalledTimes(3);
        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_dry_runs_a_rule(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        DryRunnerInterface $dryRuleRunner,
        RuleSubjectSetInterface $ruleSubjectSet1,
        RuleSubjectSetInterface $ruleSubjectSet2,
        CursorInterface $dryRunCursor1,
        CursorInterface $dryRunCursor2,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        JobStopper $jobStopper
    ) {
        $jobParameters->get('rule_codes')->willReturn(['rule1']);
        $jobParameters->get('dry_run')->willReturn(true);

        $ruleDefinitionRepository->findBy(['code' => ['rule1']], ['priority' => 'DESC'])
            ->willReturn([new RuleDefinition(), new RuleDefinition()]);

        $dryRuleRunner
            ->dryRun(Argument::type(RuleDefinition::class))
            ->shouldBeCalledTimes(4)
            ->willReturn($ruleSubjectSet1, $ruleSubjectSet2, $ruleSubjectSet1, $ruleSubjectSet2);

        $ruleSubjectSet1->getSubjectsCursor()->willReturn($dryRunCursor1);
        $ruleSubjectSet2->getSubjectsCursor()->willReturn($dryRunCursor2);

        $dryRunCursor1->count()->willReturn(10);
        $dryRunCursor2->count()->willReturn(200);

        $stepExecution->setTotalItems(210)->shouldBeCalledOnce();
        $stepExecution->setSummary(Argument::type('array'))->shouldBeCalled();

        $jobStopper->isStopping($stepExecution)->willReturn(false);

        $this->execute();
    }

    function it_stops_rules_execution_when_an_error_occurs_in_strict_mode(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        DryRunnerInterface $dryRuleRunner,
        RuleSubjectSetInterface $ruleSubjectSet1,
        RuleSubjectSetInterface $ruleSubjectSet2,
        CursorInterface $dryRunCursor1,
        CursorInterface $dryRunCursor2,
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

        $dryRuleRunner
            ->dryRun(Argument::type(RuleDefinition::class))
            ->shouldBeCalledTimes(2)
            ->willReturn($ruleSubjectSet1, $ruleSubjectSet2);

        $ruleSubjectSet1->getSubjectsCursor()->willReturn($dryRunCursor1);
        $ruleSubjectSet2->getSubjectsCursor()->willReturn($dryRunCursor2);

        $dryRunCursor1->count()->willReturn(10);
        $dryRunCursor2->count()->willReturn(20);

        $stepExecution->setTotalItems(30)->shouldBeCalledOnce();
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
        DryRunnerInterface $dryRuleRunner,
        RuleSubjectSetInterface $ruleSubjectSet1,
        RuleSubjectSetInterface $ruleSubjectSet2,
        CursorInterface $dryRunCursor1,
        CursorInterface $dryRunCursor2,
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

        $dryRuleRunner
            ->dryRun(Argument::type(RuleDefinition::class))
            ->shouldBeCalledTimes(2)
            ->willReturn($ruleSubjectSet1, $ruleSubjectSet2);

        $ruleSubjectSet1->getSubjectsCursor()->willReturn($dryRunCursor1);
        $ruleSubjectSet2->getSubjectsCursor()->willReturn($dryRunCursor2);

        $dryRunCursor1->count()->willReturn(10);
        $dryRunCursor2->count()->willReturn(20);

        $stepExecution->setTotalItems(30)->shouldBeCalledOnce();
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

    function it_can_be_stopped(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RunnerInterface $ruleRunner,
        DryRunnerInterface $dryRuleRunner,
        RuleSubjectSetInterface $ruleSubjectSet1,
        RuleSubjectSetInterface $ruleSubjectSet2,
        CursorInterface $dryRunCursor1,
        CursorInterface $dryRunCursor2,
        JobParameters $jobParameters,
        StepExecution $stepExecution,
        JobStopper $jobStopper
    ) {
        $jobParameters->get('rule_codes')->willReturn(['rule1', 'rule2']);
        $jobParameters->get('dry_run')->willReturn(false);

        $rule1 = (new RuleDefinition())->setCode('rule1');
        $rule2 = (new RuleDefinition())->setCode('rule2');

        $ruleDefinitionRepository->findBy(['code' => ['rule1', 'rule2']], ['priority' => 'DESC'])
                                 ->willReturn([$rule1, $rule2]);

        $dryRuleRunner
            ->dryRun(Argument::type(RuleDefinition::class))
            ->shouldBeCalledTimes(2)
            ->willReturn($ruleSubjectSet1, $ruleSubjectSet2);

        $ruleSubjectSet1->getSubjectsCursor()->willReturn($dryRunCursor1);
        $ruleSubjectSet2->getSubjectsCursor()->willReturn($dryRunCursor2);

        $dryRunCursor1->count()->willReturn(1);
        $dryRunCursor2->count()->willReturn(2);

        $stepExecution->setTotalItems(3)->shouldBeCalledOnce();
        $stepExecution->setSummary(Argument::type('array'))->shouldBeCalled();

        $jobStopper->isStopping($stepExecution)->shouldBeCalledTimes(3)->willReturn(false, true, false);

        $ruleRunner->run($rule1)->shouldBeCalled();
        $jobStopper->stop($stepExecution)->shouldBeCalled();
        $ruleRunner->run($rule2)->shouldNotBeCalled();

        $this->execute();
    }
}
