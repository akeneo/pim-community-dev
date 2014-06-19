<?php

namespace spec\PimEnterprise\Bundle\UserBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryOwnershipManager;

class CategoryOwnershipSubscriberSpec extends ObjectBehavior
{
    function let(CategoryOwnershipManager $ownershipManager)
    {
        $this->beConstructedWith($ownershipManager);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_post_set_data_and_post_submit_form_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                FormEvents::PRE_SET_DATA  => 'preSetData',
                FormEvents::POST_SUBMIT   => 'postSubmit'
            ]
        );
    }

    function it_adds_category_ownership_to_the_form(FormEvent $event, Form $form)
    {
        $event->getForm()->willReturn($form);
        $form->add('ownership', 'pimee_user_category_ownership')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_saves_the_defined_ownership_if_the_form_is_valid(
        FormEvent $event,
        Role $role,
        CategoryInterface $grantedCategory,
        CategoryInterface $revokedCategory,
        Form $form,
        Form $appendForm,
        Form $removeForm,
        $ownershipManager
    ) {
        $event->getForm()->willReturn($form);
        $form->isValid()->willReturn(true);

        $form->get('ownership')->willReturn($form);
        $form->get('appendCategories')->willReturn($appendForm);
        $form->get('removeCategories')->willReturn($removeForm);

        $event->getData()->willReturn($role);
        $appendForm->getData()->willReturn([$grantedCategory]);
        $removeForm->getData()->willReturn([$revokedCategory]);

        $ownershipManager->grantOwnership($role, $grantedCategory)->shouldBeCalled();
        $ownershipManager->revokeOwnership($role, $revokedCategory)->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_does_not_save_the_defined_ownership_if_the_form_is_invalid(
        FormEvent $event,
        Form $form,
        $ownershipManager
    ) {
        $event->getForm()->willReturn($form);

        $form->isValid()->willReturn(false);

        $ownershipManager->grantOwnership(Argument::cetera())->shouldNotBeCalled();
        $ownershipManager->revokeOwnership(Argument::cetera())->shouldNotBeCalled();

        $this->postSubmit($event);
    }
}
