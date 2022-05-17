<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Form\EventListener;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\UserManagement\Component\Model\Group;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Form\Type\CategoryPermissionsType;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
        Form $applyForm,
        FeatureFlags $featureFlags
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

        $featureFlags->isEnabled('permission')->willReturn(true);
        $this->beConstructedWith($accessManager, $securityFacade, $featureFlags);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
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
        $form->add('permissions', CategoryPermissionsType::class)->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_injects_defined_user_groups_in_the_form_data(
        $event,
        $category,
        $viewForm,
        $editForm,
        $ownForm,
        $accessManager,
        Group $groupA,
        Group $groupB,
        Group $groupC
    ) {
        $groupA->getType()->willReturn(Group::TYPE_DEFAULT);
        $groupB->getType()->willReturn(Group::TYPE_DEFAULT);
        $groupC->getType()->willReturn(Group::TYPE_DEFAULT);

        $accessManager->getViewUserGroups($category)->willReturn([$groupA, $groupB, $groupC]);
        $accessManager->getEditUserGroups($category)->willReturn([$groupB, $groupC]);
        $accessManager->getOwnUserGroups($category)->willReturn([$groupB, $groupC]);

        $viewForm->setData([$groupA, $groupB, $groupC])->shouldBeCalled();
        $editForm->setData([$groupB, $groupC])->shouldBeCalled();
        $ownForm->setData([$groupB, $groupC])->shouldBeCalled();

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
        $accessManager->getViewUserGroups($category)->willReturn([]);
        $accessManager->getEditUserGroups($category)->willReturn([]);
        $accessManager->getOwnUserGroups($category)->willReturn([]);

        $viewForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);
        $ownForm->getData()->willReturn(['three']);
        $applyForm->getData()->willReturn(false);

        $accessManager->setAccess($category, ['one', 'two'], ['three'], ['three'])->shouldBeCalled();

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
        $accessManager->getViewUserGroups($category)->willReturn([]);
        $accessManager->getEditUserGroups($category)->willReturn([]);
        $accessManager->getOwnUserGroups($category)->willReturn([]);

        $viewForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);
        $ownForm->getData()->willReturn(['three']);
        $applyForm->getData()->willReturn(true);

        $accessManager->setAccess($category, ['one', 'two'], ['three'], ['three'])->shouldBeCalled();
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
