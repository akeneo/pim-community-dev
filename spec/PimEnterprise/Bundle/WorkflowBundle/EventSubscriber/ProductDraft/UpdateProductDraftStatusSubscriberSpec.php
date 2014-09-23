<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductDraft;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class UpdateProductDraftStatusSubscriberSpec extends ObjectBehavior
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

    function it_subscribes_to_the_product_draft_pre_update_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductDraftEvents::PRE_UPDATE => 'update',
        ]);
    }

    function it_updates_the_product_draft_status_using_the_product_draft_form(
        $formFactory,
        $request,
        ProductDraftEvent $event,
        ProductDraft $productDraft,
        FormInterface $form
    ) {
        $event->getProductDraft()->willReturn($productDraft);
        $formFactory->create('pimee_workflow_product_draft', $productDraft)->willReturn($form);
        $form->submit($request)->shouldBeCalled();

        $this->update($event);
    }
}
