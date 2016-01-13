<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AttributeGroupPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        AttributeGroupAccessManager $manager,
        SecurityFacade $securityFacade,
        FormEvent $event,
        Form $form,
        AttributeGroupInterface $group,
        Form $viewForm,
        Form $editForm
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn($group);

        $form->isValid()->willReturn(true);
        $form->get('permissions')->willReturn($form);
        $form->get('view')->willReturn($viewForm);
        $form->get('edit')->willReturn($editForm);

        $group->getId()->willReturn(1);

        $securityFacade->isGranted(Argument::any())->willReturn(true);

        $this->beConstructedWith($manager, $securityFacade);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_pre_and_post_set_data_and_post_submit_form_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                FormEvents::PRE_SET_DATA  => 'preSetData',
                FormEvents::POST_SET_DATA => 'postSetData',
                FormEvents::POST_SUBMIT   => 'postSubmit'
            ]
        );
    }

    function it_adds_permissions_to_the_form($event, $form)
    {
        $form->add('permissions', 'pimee_enrich_attribute_group_permissions')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_injects_defined_user_groups_in_the_form_data($event, $group, $viewForm, $editForm, $manager)
    {
        $manager->getViewUserGroups($group)->willReturn(['foo', 'bar', 'baz']);
        $manager->getEditUserGroups($group)->willReturn(['bar', 'baz']);

        $viewForm->setData(['foo', 'bar', 'baz'])->shouldBeCalled();
        $editForm->setData(['bar', 'baz'])->shouldBeCalled();

        $this->postSetData($event);
    }

    function it_persists_the_selected_permissions_if_the_form_is_valid($event, $group, $viewForm, $editForm, $manager)
    {
        $viewForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);

        $manager->setAccess($group, ['one', 'two'], ['three'])->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_does_not_persist_the_selected_permissions_if_the_form_is_invalid($event, $form, $manager)
    {
        $form->isValid()->willReturn(false);

        $manager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->postSubmit($event);
    }
}
