<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Akeneo\Bundle\RuleEngineBundle\Model\Rule;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RuleExecutionSubscriberSpec extends ObjectBehavior
{
    function let(TokenStorageInterface $tokenStorage, NotifierInterface $notifier)
    {
        $this->beConstructedWith($tokenStorage, $notifier, 'Pim\Bundle\NotificationBundle\Entity\Notification');
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
        $tokenStorage,
        $notifier,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2,
        TokenInterface $token,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->shouldBeCalled()->willReturn($user);

        $notifier->notify(Argument::cetera(), [$user])->shouldBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_a_user_if_the_rules_are_executed_anonimously(
        $tokenStorage,
        $notifier,
        GenericEvent $event,
        Rule $rule1,
        Rule $rule2,
        TokenInterface $token
    ) {
        $event->getSubject()->willReturn([$rule1, $rule2]);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->shouldBeCalled()->willReturn(null);

        $event->getArgument()->shouldNotBeCalled();
        $notifier->notify()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }

    function it_does_not_notify_a_user_if_there_is_no_rules_to_execute(
        $tokenStorage,
        $notifier,
        GenericEvent $event,
        UserInterface $user,
        TokenInterface $token
    ) {
        $event->getSubject()->willReturn([]);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->shouldBeCalled()->willReturn($user);

        $notifier->notify()->shouldNotBeCalled();

        $this->afterJobExecution($event);
    }
}
