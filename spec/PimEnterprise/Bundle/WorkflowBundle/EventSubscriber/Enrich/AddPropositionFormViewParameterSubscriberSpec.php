<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\Enrich;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PropositionManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

class AddPropositionFormViewParameterSubscriberSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $formFactory,
        PropositionManager $manager
    ) {
        $this->beConstructedWith($formFactory, $manager);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_registers_to_the_enrich_pre_product_render_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            EnrichEvents::PRE_RENDER_PRODUCT_EDIT => 'addPropositionFormView',
        ]);
    }

    function it_adds_a_proposition_form_view_to_the_product_edit_parameters(
        $manager,
        $formFactory,
        GenericEvent $event,
        ProductInterface $product,
        Proposition $proposition,
        FormInterface $form,
        FormView $view
    ) {
        $event->getArgument('parameters')->willReturn([
            'product' => $product,
        ]);


        $manager->findOrCreate($product)->willReturn($proposition);
        $formFactory->create('pimee_workflow_proposition', $proposition)->willReturn($form);
        $form->createView()->willReturn($view);

        $event
            ->setArgument('parameters', [
                'product' => $product,
                'propositionForm' => $view,
            ])
            ->shouldBeCalled();

        $this->addPropositionFormView($event);
    }

    function it_does_nothing_if_event_does_not_have_a_parameters_argument(
        GenericEvent $event
    ) {
        $event->getArgument('parameters')->willThrow(new \InvalidArgumentException());
        $event->setArgument('parameters', Argument::any())->shouldNotBeCalled();

        $this->addPropositionFormView($event);
    }

    function it_does_nothing_if_parameters_does_not_have_a_product(
        GenericEvent $event
    ) {
        $event->getArgument('parameters')->willReturn([]);
        $event->setArgument('parameters', Argument::any())->shouldNotBeCalled();

        $this->addPropositionFormView($event);
    }
}
