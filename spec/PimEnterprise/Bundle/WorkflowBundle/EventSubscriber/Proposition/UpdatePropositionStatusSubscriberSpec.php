<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Proposition;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvents;
use PimEnterprise\Bundle\WorkflowBundle\Proposition\PropositionEvent;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class UpdatePropositionStatusSubscriberSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $formFactory,
        ContainerInterface $container,
        Request $request
    ) {
        $container->get('request')->willReturn($request);

        $this->beConstructedWith($formFactory, $container);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_proposition_pre_update_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            PropositionEvents::PRE_UPDATE => 'update',
        ]);
    }

    function it_updates_the_proposition_status_using_the_proposition_form(
        $formFactory,
        $request,
        PropositionEvent $event,
        Proposition $proposition,
        FormInterface $form
    ) {
        $event->getProposition()->willReturn($proposition);
        $formFactory->create('pimee_workflow_proposition', $proposition)->willReturn($form);
        $form->submit($request)->shouldBeCalled();

        $this->update($event);
    }
}
