<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CategoryPermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        CategoryAccessManager $accessManager,
        SecurityFacade $securityFacade,
        FormEvent $event,
        CategoryInterface $category,
        Form $form,
        Form $viewForm,
        Form $editForm,
        Form $ownForm,
        Form $applyForm
    ) {
        $securityFacade->isGranted(Argument::any())->willReturn(true);

        $event->getForm()->willReturn($form);
        $event->getData()->willReturn($category);

        $form->isValid()->willReturn(true);
        $form->get('permissions')->willReturn($form);
        $form->get('view')->willReturn($viewForm);
        $form->get('edit')->willReturn($editForm);
        $form->get('own')->willReturn($ownForm);
        $form->get('apply_on_children')->willReturn($applyForm);

        $category->isRoot()->willReturn(true);
        $category->getId()->willReturn(1);

        $this->beConstructedWith($accessManager, $securityFacade);
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

    function it_adds_permissions_to_the_form($event, $form)
    {
        $form->add('permissions', 'pimee_enrich_category_permissions')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_injects_defined_user_groups_in_the_form_data(
        $event,
        $category,
        $viewForm,
        $editForm,
        $ownForm,
        $accessManager
    ) {
        $accessManager->getViewUserGroups($category)->willReturn(['foo', 'bar', 'baz']);
        $accessManager->getEditUserGroups($category)->willReturn(['bar', 'baz']);
        $accessManager->getOwnUserGroups($category)->willReturn(['bar', 'baz']);

        $viewForm->setData(['foo', 'bar', 'baz'])->shouldBeCalled();
        $editForm->setData(['bar', 'baz'])->shouldBeCalled();
        $ownForm->setData(['bar', 'baz'])->shouldBeCalled();

        $this->postSetData($event);
    }

    function it_persists_the_selected_permissions_if_the_form_is_valid(
        $event,
        $category,
        $viewForm,
        $editForm,
        $ownForm,
        $applyForm,
        $accessManager
    ) {
        $viewForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);
        $ownForm->getData()->willReturn(['three']);
        $applyForm->getData()->willReturn(false);

        $accessManager->setAccess($category, ['one', 'two'], ['three'], ['three'], true)->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_applies_the_new_permissions_on_children(
        $event,
        $category,
        $viewForm,
        $editForm,
        $ownForm,
        $applyForm,
        $accessManager
    ) {
        $viewForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);
        $ownForm->getData()->willReturn(['three']);
        $applyForm->getData()->willReturn(true);

        $accessManager->setAccess($category, ['one', 'two'], ['three'], ['three'], true)->shouldBeCalled();
        $accessManager
            ->updateChildrenAccesses($category, ['one', 'two'], ['three'], ['three'], [], [], [])
            ->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_does_not_persist_the_selected_permissions_if_the_form_is_invalid($event, $form, $accessManager)
    {
        $form->isValid()->willReturn(false);

        $accessManager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->postSubmit($event);
    }
}
