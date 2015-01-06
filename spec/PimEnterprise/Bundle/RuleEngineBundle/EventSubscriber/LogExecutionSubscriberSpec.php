<?php

namespace spec\PimEnterprise\Bundle\RuleEngineBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\SelectedRuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleSubjectSetInterface;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class LogExecutionSubscriberSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger) {
        $this->beConstructedWith($logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\EventSubscriber\LogExecutionSubscriber');
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
        $subjectSet->getSubjects()->willReturn([$subject]);
        $subjectSet->getSkippedSubjects()->willReturn([]);

        $this->postApply($event);
    }

    function it_logs_post_apply_rule_error(
        $logger,
        SelectedRuleEvent $event,
        RuleDefinitionInterface $definition,
        RuleSubjectSetInterface $subjectSet,
        ProductInterface $subject
    ) {
        $event->getDefinition()->willReturn($definition);
        $event->getSubjectSet()->willReturn($subjectSet);
        $logger->info(Argument::any())->shouldBeCalled();
        $subjectSet->getSubjects()->willReturn([$subject]);
        $subjectSet->getSkippedSubjects()->willReturn([['subject' => $subject, 'reasons' => ['My name should be shorter']]]);

        $definition->getCode()->willReturn('rule_code');
        $subject->getId()->willReturn(42);
        $logger->error('Rule event: rule_code postApply : 1 subjects skipped')->shouldBeCalled();
        $logger->error('Rule event: rule_code postApply : subject "42" has been skipped due to "My name should be shorter"')->shouldBeCalled();

        $this->postApply($event);
    }
}
