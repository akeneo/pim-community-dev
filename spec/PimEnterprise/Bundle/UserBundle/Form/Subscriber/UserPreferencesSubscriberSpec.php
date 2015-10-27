<?php

namespace spec\PimEnterprise\Bundle\UserBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class UserPreferencesSubscriberSpec extends ObjectBehavior
{
    function let(CategoryAccessRepository $categoryAccessRepo)
    {
        $this->beConstructedWith($categoryAccessRepo);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_has_subscribed_event()
    {
        $events = $this->getSubscribedEvents();
        $events->shouldHaveCount(1);
        $events[FormEvents::PRE_SET_DATA]->shouldBe('preSetData');
    }

    function it_adds_proposalsToReviewNotification_field_if_the_user_is_at_least_own_of_one_category(
        $categoryAccessRepo,
        FormEvent $event,
        UserInterface $user,
        FormInterface $form
    ) {
        $event->getData()->willReturn($user);
        $event->getForm()->willReturn($form);

        $categoryAccessRepo->isOwner($user)->willReturn(true);

        $form->add(
            'proposalsToReviewNotification',
            'switch',
            [
                'label'    => 'user.proposals.notifications.to_review',
                'required' => false,
            ]
        )->shouldBeCalled();

        $categoryAccessRepo->getGrantedCategoryCodes(Argument::cetera())->willReturn([]);

        $this->preSetData($event);
    }

    function it_adds_proposalsStateNotification_field_if_the_user_can_edit_at_least_one_category(
        $categoryAccessRepo,
        FormEvent $event,
        UserInterface $user,
        FormInterface $form
    ) {
        $event->getData()->willReturn($user);
        $event->getForm()->willReturn($form);

        $categoryAccessRepo->isOwner($user)->willReturn(false);

        $categoryAccessRepo->getGrantedCategoryCodes(Argument::cetera())->willReturn([]);

        $categoryAccessRepo
            ->getGrantedCategoryCodes($user, Attributes::EDIT_ITEMS)
            ->willReturn(['high_tech', 'tv']);
        $categoryAccessRepo
            ->getGrantedCategoryCodes($user, Attributes::OWN_PRODUCTS)
            ->willReturn([]);

        $form->add(
            'proposalsStateNotification',
            'switch',
            [
                'label'    => 'user.proposals.notifications.state',
                'required' => false,
            ]
        )->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_does_not_add_proposalsToReviewNotification_field_if_the_user_does_not_own_a_category(
        $categoryAccessRepo,
        FormEvent $event,
        UserInterface $user,
        FormInterface $form
    ) {
        $event->getData()->willReturn($user);
        $event->getForm()->willReturn($form);

        $categoryAccessRepo->isOwner($user)->willReturn(false);

        $form->add(
            'proposalsToReviewNotification',
            'switch',
            [
                'label'    => 'user.proposals.notifications.to_review',
                'required' => false,
            ]
        )->shouldNotBeCalled();

        $categoryAccessRepo->getGrantedCategoryCodes(Argument::cetera())->willReturn([]);

        $this->preSetData($event);
    }

    function it_does_not_add_proposalsStateNotification_field_if_the_user_all_categories_he_can_edit(
        $categoryAccessRepo,
        FormEvent $event,
        UserInterface $user,
        FormInterface $form
    ) {
        $event->getData()->willReturn($user);
        $event->getForm()->willReturn($form);

        $categoryAccessRepo->isOwner($user)->willReturn(false);

        $categoryAccessRepo->getGrantedCategoryCodes(Argument::cetera())->willReturn([]);

        $categoryAccessRepo
            ->getGrantedCategoryCodes($user, Attributes::EDIT_ITEMS)
            ->willReturn(['high_tech', 'tv']);
        $categoryAccessRepo
            ->getGrantedCategoryCodes($user, Attributes::OWN_PRODUCTS)
            ->willReturn(['high_tech', 'tv']);

        $form->add(
            'proposalsStateNotification',
            'switch',
            [
                'label'    => 'user.proposals.notifications.state',
                'required' => false,
            ]
        )->shouldNotBeCalled();

        $this->preSetData($event);
    }
}
