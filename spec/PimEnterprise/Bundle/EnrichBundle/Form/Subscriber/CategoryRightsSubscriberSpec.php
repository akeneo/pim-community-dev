<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Entity\Category;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryRightsSubscriberSpec extends ObjectBehavior
{
    function let(CategoryAccessManager $accessManager)
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

    function it_adds_rights_to_the_form(FormEvent $event, Form $form, Category $category)
    {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn($category);
        $category->isRoot()->willReturn(true);
        $category->getId()->willReturn(1);

        $form->add('rights', 'pimee_enrich_category_rights')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_injects_defined_roles_in_the_form_data(
        FormEvent $event,
        Category $category,
        Form $form,
        Form $viewForm,
        Form $editForm,
        $accessManager
    ) {
        $event->getData()->willReturn($category);
        $category->isRoot()->willReturn(true);
        $category->getId()->willReturn(1);

        $event->getForm()->willReturn($form);
        $form->get('rights')->willReturn($form);
        $form->get('view')->willReturn($viewForm);
        $form->get('edit')->willReturn($editForm);

        $accessManager->getViewRoles($category)->willReturn(['foo', 'bar', 'baz']);
        $accessManager->getEditRoles($category)->willReturn(['bar', 'baz']);

        $viewForm->setData(['foo', 'bar', 'baz'])->shouldBeCalled();
        $editForm->setData(['bar', 'baz'])->shouldBeCalled();

        $this->postSetData($event);
    }

    function it_persists_the_selected_rights_if_the_form_is_valid(
        FormEvent $event,
        Category $category,
        Form $form,
        Form $viewForm,
        Form $editForm,
        $accessManager
    ) {
        $event->getData()->willReturn($category);
        $category->isRoot()->willReturn(true);
        $category->getId()->willReturn(1);

        $event->getForm()->willReturn($form);
        $form->isValid()->willReturn(true);
        $form->get('rights')->willReturn($form);
        $form->get('view')->willReturn($viewForm);
        $form->get('edit')->willReturn($editForm);

        $viewForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);


        $accessManager->setAccess($category, ['one', 'two'], ['three'])->shouldBeCalled();

        $this->postSubmit($event);
    }
}
