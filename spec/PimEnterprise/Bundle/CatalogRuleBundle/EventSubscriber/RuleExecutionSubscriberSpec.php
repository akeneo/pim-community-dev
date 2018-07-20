<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Akeneo\Bundle\RuleEngineBundle\Model\Rule;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\User\ChainUserProvider;

class RuleExecutionSubscriberSpec extends ObjectBehavior
{
    function let(ChainUserProvider $chainUserProvider, NotifierInterface $notifier)
    {
        $this->beConstructedWith($chainUserProvider, $notifier, 'Pim\Bundle\NotificationBundle\Entity\Notification');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber\RuleExecutionSubscriber');
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_POST_EXECUTE_ALL_rule_event ()
    {
        $this->getSubscribedEvents()->shouldReturn(
            ['pim_rule_engine.rule.post_execute_all' => 'afterJobExecution']
        );
    }

    function it_notifies_an_user_if_the_rules_are_executed_with_its_user_name(
        $notifier,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $event->getArgument('username')->willReturn('Morty');
        $notifier->notify(Argument::any(), ['Morty'])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_an_user_if_the_rules_are_executed_anonimously(
        $notifier,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $event->getArgument('username')->willReturn(null);

        $notifier->notify()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_an_user_if_there_is_no_rules_to_execute(
        $notifier,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn([]);
        $event->getArgument('username')->willReturn('Morty');

        $notifier->notify()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }
}
