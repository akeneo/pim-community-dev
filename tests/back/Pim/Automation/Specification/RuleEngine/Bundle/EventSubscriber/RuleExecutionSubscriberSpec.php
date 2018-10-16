<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Bundle\EventSubscriber;

use Akeneo\Pim\Automation\RuleEngine\Bundle\EventSubscriber\RuleExecutionSubscriber;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class RuleExecutionSubscriberSpec extends ObjectBehavior
{
    function let(NotifierInterface $notifier)
    {
        $this->beConstructedWith($notifier, Notification::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RuleExecutionSubscriber::class);
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
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
