<?php

namespace spec\Akeneo\Tool\Bundle\RuleEngineBundle\Runner;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\DryRunnerInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChainedRunnerSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->beConstructedWith($eventDispatcher,$logger, false);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Tool\Bundle\RuleEngineBundle\Runner\ChainedRunner');
    }

    function it_should_be_a_bulk_runner_and_a_dry_runner()
    {
        $this->shouldImplement('Akeneo\Tool\Bundle\RuleEngineBundle\Runner\RunnerInterface');
        $this->shouldImplement('Akeneo\Tool\Bundle\RuleEngineBundle\Runner\DryRunnerInterface');
        $this->shouldImplement('Akeneo\Tool\Bundle\RuleEngineBundle\Runner\BulkRunnerInterface');
        $this->shouldImplement('Akeneo\Tool\Bundle\RuleEngineBundle\Runner\BulkDryRunnerInterface');
    }

    function it_supports_all_rules(RuleDefinitionInterface $definition)
    {
        $this->supports($definition)->shouldReturn(true);
    }

    function it_runs_a_rule(
        $eventDispatcher,
        RuleDefinitionInterface $rule,
        RunnerInterface $runner1,
        RunnerInterface $runner2
    ) {
        $runner1->supports($rule)->willReturn(false);
        $runner2->supports($rule)->willReturn(true);
        $runner1->run(Argument::cetera())->willReturn('Runner 1 launched');
        $runner2->run(Argument::cetera())->willReturn('Runner 2 launched');

        $this->addRunner($runner1);
        $this->addRunner($runner2);

        $eventDispatcher->dispatch(Argument::cetera(), 'pim_rule_engine.rule.pre_execute')->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::cetera(), 'pim_rule_engine.rule.post_execute')->shouldBeCalled();

        $this->run($rule)->shouldReturn('Runner 2 launched');
    }

    function it_runs_several_rules(
        $eventDispatcher,
        RuleDefinitionInterface $rule1,
        RuleDefinitionInterface $rule2,
        RunnerInterface $runner1,
        RunnerInterface $runner2
    ) {
        $rule1->getCode()->willReturn('rule1');
        $rule2->getCode()->willReturn('rule2');

        $runner1->supports($rule1)->willReturn(true);
        $runner1->supports($rule2)->willReturn(false);
        $runner2->supports($rule1)->willReturn(false);
        $runner2->supports($rule2)->willReturn(true);
        $runner1->run(Argument::cetera())->willReturn('Runner 1 launched');
        $runner2->run(Argument::cetera())->willReturn('Runner 2 launched');

        $this->addRunner($runner1);
        $this->addRunner($runner2);

        $eventDispatcher->dispatch(Argument::cetera(), 'pim_rule_engine.rule.pre_execute')->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::cetera(), 'pim_rule_engine.rule.post_execute')->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::cetera(), 'pim_rule_engine.rule.pre_execute_all')->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::cetera(), 'pim_rule_engine.rule.post_execute_all')->shouldBeCalled();

        $this->runAll([$rule1, $rule2], ['username' => Argument::cetera()])->shouldReturn([
            'rule1' => 'Runner 1 launched',
            'rule2' => 'Runner 2 launched'
        ]);
    }

    function it_logs_an_error_when_no_runner_supports_the_rule_and_chained_runner_does_not_stop_on_error(
        $logger,
        RuleDefinitionInterface $rule,
        RunnerInterface $runner1,
        RunnerInterface $runner2
    ) {
        $rule->getCode()->willReturn('therule');

        $runner1->supports($rule)->willReturn(false);
        $runner2->supports($rule)->willReturn(false);

        $this->addRunner($runner1);
        $this->addRunner($runner2);

        $logger->error(Argument::any())->shouldBeCalled();

        $this->run($rule);
    }

    function it_throws_an_exception_when_no_runner_supports_the_rule_and_chained_runner_stops_on_error(
        $eventDispatcher,
        $logger,
        RuleDefinitionInterface $rule,
        RunnerInterface $runner1,
        RunnerInterface $runner2
    ) {
        $this->beConstructedWith($eventDispatcher,$logger, true);
        $rule->getCode()->willReturn('therule');

        $runner1->supports($rule)->willReturn(false);
        $runner2->supports($rule)->willReturn(false);

        $this->addRunner($runner1);
        $this->addRunner($runner2);

        $this
            ->shouldThrow(new \LogicException('No runner available for the rule "therule".'))
            ->during('run', [$rule]);

        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
    }

    function it_dry_runs_a_rule(
        RuleDefinitionInterface $rule,
        DryRunnerInterface $runner1,
        RunnerInterface $runner2,
        DryRunnerInterface $runner3,
        RuleSubjectSetInterface $subject1,
        RuleSubjectSetInterface $subject2,
        RuleSubjectSetInterface $subject3
    ) {
        $runner1->supports($rule)->willReturn(false);
        $runner2->supports($rule)->willReturn(true);
        $runner3->supports($rule)->willReturn(true);
        $runner1->dryRun(Argument::cetera())->willReturn($subject1);
        $runner2->run(Argument::cetera())->willReturn($subject2);
        $runner3->dryRun(Argument::cetera())->willReturn($subject3);

        $this->addRunner($runner1);
        $this->addRunner($runner2);
        $this->addRunner($runner3);
        $this->dryRun($rule)->shouldReturn($subject3);
    }

    function it_dry_runs_several_rules(
        RuleDefinitionInterface $rule1,
        RuleDefinitionInterface $rule2,
        DryRunnerInterface $runner1,
        DryRunnerInterface $runner2,
        RuleSubjectSetInterface $subject1,
        RuleSubjectSetInterface $subject2
    ) {
        $rule1->getCode()->willReturn('rule1');
        $rule2->getCode()->willReturn('rule2');

        $runner1->supports($rule1)->willReturn(true);
        $runner1->supports($rule2)->willReturn(false);
        $runner2->supports($rule1)->willReturn(false);
        $runner2->supports($rule2)->willReturn(true);
        $runner1->dryRun(Argument::cetera())->willReturn($subject1);
        $runner2->dryRun(Argument::cetera())->willReturn($subject2);

        $this->addRunner($runner1);
        $this->addRunner($runner2);

        $this->dryRunAll([$rule1, $rule2])->shouldReturn([
            'rule1' => $subject1,
            'rule2' => $subject2
        ]);
    }

    function it_logs_an_error_when_no_dry_runner_supports_the_rule_and_chained_runner_does_not_stop_on_error(
        $logger,
        RuleDefinitionInterface $rule,
        DryRunnerInterface $runner1,
        DryRunnerInterface $runner2
    ) {
        $rule->getCode()->willReturn('therule');

        $runner1->supports($rule)->willReturn(false);
        $runner2->supports($rule)->willReturn(false);

        $this->addRunner($runner1);
        $this->addRunner($runner2);

        $logger->error(Argument::any())->shouldBeCalled();

        $this->dryRun($rule);
    }

    function it_throws_an_exception_when_no_dry_runner_supports_the_rule_and_chained_runner_stops_on_error(
        $eventDispatcher,
        $logger,
        RuleDefinitionInterface $rule,
        DryRunnerInterface $runner1,
        DryRunnerInterface $runner2
    ) {
        $this->beConstructedWith($eventDispatcher,$logger, true);
        $rule->getCode()->willReturn('therule');

        $runner1->supports($rule)->willReturn(false);
        $runner2->supports($rule)->willReturn(false);

        $this->addRunner($runner1);
        $this->addRunner($runner2);

        $this
            ->shouldThrow(new \LogicException('No dry runner available for the rule "therule".'))
            ->during('dryRun', [$rule]);
    }
}
