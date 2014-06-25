<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

class AttributeGroupPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(AttributeGroupAccessManager $accessManager)
    {
        $this->beConstructedWith($accessManager);
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
                FormEvents::POST_SET_DATA => 'postSetData',
                FormEvents::POST_SUBMIT   => 'postSubmit'
            ]
        );
    }

    function it_adds_permissions_to_the_form(FormEvent $event, Form $form)
    {
        $event->getForm()->willReturn($form);
        $form->add('permissions', 'pimee_enrich_attribute_group_permissions')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_injects_defined_roles_in_the_form_data(
        FormEvent $event,
        AttributeGroup $group,
        Form $form,
        Form $viewForm,
        Form $editForm,
        $accessManager
    ) {
        $event->getData()->willReturn($group);
        $group->getId()->willReturn(1);

        $event->getForm()->willReturn($form);
        $form->get('permissions')->willReturn($form);
        $form->get('view')->willReturn($viewForm);
        $form->get('edit')->willReturn($editForm);

        $accessManager->getViewRoles($group)->willReturn(['foo', 'bar', 'baz']);
        $accessManager->getEditRoles($group)->willReturn(['bar', 'baz']);

        $viewForm->setData(['foo', 'bar', 'baz'])->shouldBeCalled();
        $editForm->setData(['bar', 'baz'])->shouldBeCalled();

        $this->postSetData($event);
    }

    function it_persists_the_selected_permissions_if_the_form_is_valid(
        FormEvent $event,
        AttributeGroup $group,
        Form $form,
        Form $viewForm,
        Form $editForm,
        $accessManager
    ) {
        $event->getData()->willReturn($group);
        $group->getId()->willReturn(1);

        $event->getForm()->willReturn($form);
        $form->isValid()->willReturn(true);
        $form->get('permissions')->willReturn($form);
        $form->get('view')->willReturn($viewForm);
        $form->get('edit')->willReturn($editForm);

        $viewForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);

        $accessManager->setAccess($group, ['one', 'two'], ['three'])->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_does_not_persist_the_selected_permissions_if_the_form_is_invalid(
        FormEvent $event,
        AttributeGroup $group,
        Form $form,
        $accessManager
    ) {
        $event->getData()->willReturn($group);

        $event->getForm()->willReturn($form);
        $form->isValid()->willReturn(false);

        $accessManager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->postSubmit($event);
    }
}
