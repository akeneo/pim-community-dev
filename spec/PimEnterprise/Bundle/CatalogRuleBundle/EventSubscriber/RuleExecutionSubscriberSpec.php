<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Akeneo\Bundle\RuleEngineBundle\Model\Rule;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
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

    function it_notify_a_user_if_the_rules_are_executed_with_its_user_name(
        $chainUserProvider,
        $notifier,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $event->getArgument('username')->willReturn(Argument::cetera());
        $chainUserProvider->loadUserByUsername(Argument::cetera())->willReturn($user);

        $notifier->notify(Argument::cetera(), [$user])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_a_user_if_the_rules_are_executed_anonimously(
        $notifier,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $event->getArgument('username')->willThrow(\InvalidArgumentException::class);

        $notifier->notify()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_a_user_if_the_user_does_not_exist(
        $notifier,
        $chainUserProvider,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $event->getArgument('username')->willReturn(Argument::any());

        $chainUserProvider->loadUserByUsername(Argument::any())->willThrow(UsernameNotFoundException::class);

        $notifier->notify()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_a_user_if_there_is_no_rules_to_execute(
        $notifier,
        GenericEvent $event
    ) {
        $event->getSubject()->willReturn([]);
        $event->getArgument('username')->willReturn(Argument::any());

        $notifier->notify()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }
}
