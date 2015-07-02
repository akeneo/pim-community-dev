<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Enrich;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormFactoryInterface;

class AddProductDraftFormViewParameterSubscriberSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $formFactory, ProductDraftManager $manager)
    {
        $this->beConstructedWith($formFactory, $manager);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_registers_to_the_enrich_pre_product_render_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            ProductEvents::PRE_RENDER_EDIT => 'addProductDraftFormView',
        ]);
    }

    function it_adds_a_product_draft_form_view_to_the_product_edit_parameters(
        $manager,
        $formFactory,
        GenericEvent $event,
        ProductInterface $product,
        ProductDraftInterface $productDraft
    ) {
        $event->getArgument('parameters')->willReturn([
            'product' => $product,
        ]);

        $manager->findOrCreate($product)->willReturn($productDraft);

        $event
            ->setArgument('parameters', [
                'product' => $product,
                'productDraft' => $productDraft,
            ])
            ->shouldBeCalled();

        $this->addProductDraftFormView($event);
    }

    function it_does_nothing_if_event_does_not_have_a_parameters_argument(GenericEvent $event)
    {
        $event->getArgument('parameters')->willThrow(new \InvalidArgumentException());
        $event->setArgument('parameters', Argument::any())->shouldNotBeCalled();

        $this->addProductDraftFormView($event);
    }

    function it_does_nothing_if_parameters_does_not_have_a_product(GenericEvent $event)
    {
        $event->getArgument('parameters')->willReturn([]);
        $event->setArgument('parameters', Argument::any())->shouldNotBeCalled();

        $this->addProductDraftFormView($event);
    }
}
