<?php

namespace spec\Akeneo\Tool\Bundle\RuleEngineBundle\EventSubscriber;

use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\SkippedSubjectRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\EventSubscriber\LogExecutionSubscriber;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogExecutionSubscriberSpec extends ObjectBehavior
{
    function let(LoggerInterface $logger)
    {
        $this->beConstructedWith($logger, LogExecutionSubscriber::LOGGING_LEVEL_INFO);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LogExecutionSubscriber::class);
    }

    function it_is_a_subscriber()
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    function it_logs_pre_apply_rule_information(
        $logger,
        SelectedRuleEvent $event,
        RuleDefinitionInterface $definition,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $subject
    ) {
        $event->getDefinition()->willReturn($definition);
        $event->getSubjectSet()->willReturn($subjectSet);
        $subjectSet->getSubjectsCursor()->willReturn([$subject]);
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
        $subject->getUuid()->willReturn(Uuid::fromString('359a2a04-5fa4-4f15-9c08-09b819327c8f'));

        $logger->warning(
            'Rule "rule_code", event "pim_rule_engine.rule.skip": subject "359a2a04-5fa4-4f15-9c08-09b819327c8f" has been skipped due to "My name should be shorter".'
        )->shouldBeCalled();

        $this->skip($event);
    }

    function it_logs_if_logger_severity_is_info(
        $logger,
        SelectedRuleEvent $event,
        RuleDefinitionInterface $definition,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $subject
    ) {
        $this->beConstructedWith($logger, LogExecutionSubscriber::LOGGING_LEVEL_INFO);

        $event->getDefinition()->willReturn($definition);
        $event->getSubjectSet()->willReturn($subjectSet);
        $subjectSet->getSubjectsCursor()->willReturn([$subject]);

        $logger->info(Argument::any())->shouldBeCalled();

        $this->postSelect($event);
    }

    function it_logs_if_logger_severity_is_lower_than_info(
        $logger,
        SelectedRuleEvent $event,
        RuleDefinitionInterface $definition,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $subject
    ) {
        $this->beConstructedWith($logger, LogExecutionSubscriber::LOGGING_LEVEL_DEBUG);

        $event->getDefinition()->willReturn($definition);
        $event->getSubjectSet()->willReturn($subjectSet);
        $subjectSet->getSubjectsCursor()->willReturn([$subject]);

        $logger->info(Argument::any())->shouldBeCalled();

        $this->postSelect($event);
    }

    function it_does_not_log_if_logger_severity_is_higher_than_info(
        $logger,
        SelectedRuleEvent $event
    ) {
        $this->beConstructedWith($logger, 'a_logging_level');

        $logger->info(Argument::any())->shouldNotBeCalled();

        $this->postSelect($event);
    }
}
