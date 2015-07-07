<?php

namespace spec\PimEnterprise\Bundle\ImportExportBundle\Form\Subscriber;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class JobProfilePermissionsSubscriberSpec extends ObjectBehavior
{
    function let(
        JobProfileAccessManager $manager,
        SecurityFacade $securityFacade,
        GroupRepository $userGroupRepository,
        FormEvent $event,
        Form $form,
        JobInstance $jobInstance,
        Form $executeForm,
        Form $editForm
    ) {
        $this->beConstructedWith($manager, $securityFacade, $userGroupRepository);

        $securityFacade->isGranted(Argument::any())->willReturn(true);

        $jobInstance->getId()->willReturn(1);
        $jobInstance->getType()->willReturn('import');

        $event->getData()->willReturn($jobInstance);
        $event->getForm()->willReturn($form);

        $form->isValid()->willReturn(true);
        $form->get('permissions')->willReturn($form);
        $form->get('execute')->willReturn($executeForm);
        $form->get('edit')->willReturn($editForm);
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
        $form->add('permissions', 'pimee_import_export_job_profile_permissions')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_injects_defined_user_groups_in_the_form_data($event, $jobInstance, $executeForm, $editForm, $manager)
    {
        $manager->getExecuteUserGroups($jobInstance)->willReturn(['foo', 'bar', 'baz']);
        $manager->getEditUserGroups($jobInstance)->willReturn(['bar', 'baz']);

        $executeForm->setData(['foo', 'bar', 'baz'])->shouldBeCalled();
        $editForm->setData(['bar', 'baz'])->shouldBeCalled();

        $this->postSetData($event);
    }

    function it_persists_all_user_groups_on_creation($event, $form, $jobInstance, $userGroupRepository, $manager)
    {
        $jobInstance->getId()->willReturn(null);
        $userGroupRepository->findAll()->willReturn(['foo']);

        $manager->setAccess($jobInstance, ['foo'], ['foo'])->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_persists_the_selected_permissions_if_the_form_is_valid(
        $event,
        $jobInstance,
        $executeForm,
        $editForm,
        $manager
    ) {
        $executeForm->getData()->willReturn(['one', 'two']);
        $editForm->getData()->willReturn(['three']);

        $manager->setAccess($jobInstance, ['one', 'two'], ['three'])->shouldBeCalled();

        $this->postSubmit($event);
    }

    function it_does_not_persist_the_selected_permissions_if_the_form_is_invalid($event, $form, $manager)
    {
        $form->isValid()->willReturn(false);
        $manager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->postSubmit($event);
    }

    function it_does_not_persist_the_selected_permissions_if_user_has_not_permissions_to_do_it(
        $event,
        $form,
        $manager,
        $securityFacade
    ) {
        $securityFacade->isGranted(Argument::any())->willReturn(false);
        $form->isValid()->willReturn(true);

        $manager->setAccess(Argument::cetera())->shouldNotBeCalled();

        $this->postSubmit($event);
    }
}
