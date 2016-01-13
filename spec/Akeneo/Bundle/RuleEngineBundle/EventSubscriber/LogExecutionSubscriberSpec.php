<?php

namespace spec\Akeneo\Bundle\RuleEngineBundle\EventSubscriber;

use Akeneo\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class LogExecutionSubscriberSpec extends ObjectBehavior
{
    function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\EventSubscriber\LogExecutionSubscriber');
    }

    function it_is_a_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_logs_pre_apply_rule_information(
        $logger,
        SelectedRuleEvent $event,
        RuleDefinitionInterface $definition,
        RuleSubjectSetInterface $subjectSet
    ) {
        $event->getDefinition()->willReturn($definition);
        $event->getSubjectSet()->willReturn($subjectSet);
        $logger->info(Argument::any())->shouldBeCalled();

        $this->preApply($event);
    }

    function it_logs_post_apply_rule_information(
        $logger,
        SelectedRuleEvent $event,
        RuleDefinitionInterface $definition,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $subject
    ) {
        $event->getDefinition()->willReturn($definition);
        $event->getSubjectSet()->willReturn($subjectSet);
        $logger->info(Argument::any())->shouldBeCalled();
        $subjectSet->getSubjectsCursor()->willReturn([$subject]);
        $this->postApply($event);
    }

    function it_logs_post_apply_rule_error(
        $logger,
        SkippedSubjectRuleEvent $event,
        RuleDefinitionInterface $definition,
        ProductInterface $subject
    ) {
        $event->getDefinition()->willReturn($definition);
        $event->getSubject()->willReturn($subject);
        $reasons = ['My name should be shorter'];
        $event->getReasons()->willReturn($reasons);

        $definition->getCode()->willReturn('rule_code');
        $subject->getId()->willReturn(42);

        $logger->warning('Rule "rule_code", event "pim_rule_engine.rule.skip": subject "42" has been skipped due to "My name should be shorter".')->shouldBeCalled();

        $this->skip($event);
    }
}
