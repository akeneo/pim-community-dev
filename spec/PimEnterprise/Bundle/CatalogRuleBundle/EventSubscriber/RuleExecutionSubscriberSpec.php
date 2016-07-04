<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Akeneo\Bundle\RuleEngineBundle\Model\Rule;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class RuleExecutionSubscriberSpec extends ObjectBehavior
{
    function let(NotificationFactoryRegistry $factoryRegistry, NotifierInterface $notifier)
    {
        $this->beConstructedWith($factoryRegistry, $notifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber\RuleExecutionSubscriber');
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_after_command_execution_rule_event ()
    {
        $this->getSubscribedEvents()->shouldReturn(
            ['pim_rule_engine.command.after_execution' => 'afterJobExecution']
        );
    }

    function it_notify_a_user_if_the_rules_are_executed_with_its_user_name(
        $factoryRegistry,
        $notifier,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2,
        UserInterface $user,
        NotificationFactoryInterface $factory,
        NotificationInterface $notification
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $event->hasArgument('user')->willReturn(true);

        $event->getArgument('user')->shouldBeCalled()->willReturn($user);
        $factoryRegistry->get('rule')->shouldBeCalled()->willReturn($factory);
        $factory->create(2)->shouldBeCalled()->willReturn($notification);

        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_a_user_if_the_rules_are_executed_anonimously(
        $factoryRegistry,
        $notifier,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2,
        NotificationFactoryInterface $factory
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $event->hasArgument('user')->willReturn(false);

        $event->getArgument()->shouldNotBeCalled();
        $factoryRegistry->get()->shouldNotBeCalled();
        $factory->create()->shouldNotBeCalled();

        $notifier->notify()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_a_user_if_there_is_no_rules_to_execute(
        $factoryRegistry,
        $notifier,
        GenericEvent $event,
        UserInterface $user,
        NotificationFactoryInterface $factory
    ) {
        $event->getSubject()->willReturn([]);
        $event->hasArgument('user')->willReturn(true);

        $event->getArgument('user')->shouldBeCalled()->willReturn($user);
        $factoryRegistry->get()->shouldNotBeCalled();
        $factory->create()->shouldNotBeCalled();

        $notifier->notify()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_throws_an_exception_if_the_factory_does_not_exists(
        $factoryRegistry,
        $notifier,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2,
        UserInterface $user,
        NotificationFactoryInterface $factory,
        NotificationInterface $notification
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $event->hasArgument('user')->willReturn(true);

        $event->getArgument('user')->shouldBeCalled()->willReturn($user);
        $factoryRegistry->get('rule')->shouldBeCalled()->willReturn(null);

        $this
            ->shouldThrow('\LogicException')
            ->during('afterJobExecution', [$event]);

        $factory->create()->shouldNotBeCalled();
        $notifier->notify()->shouldNotBeCalled();
    }
}
