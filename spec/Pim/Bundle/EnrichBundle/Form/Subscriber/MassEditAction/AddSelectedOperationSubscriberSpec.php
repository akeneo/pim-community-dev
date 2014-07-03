<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber\MassEditAction;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\EnrichBundle\MassEditAction\Operator\AbstractMassEditOperator;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\MassEditOperationInterface;
use Symfony\Component\Form\FormInterface;

class AddSelectedOperationSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber_interface()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_form_post_set_data_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['form.post_set_data' => 'postSetData']);
    }

    function it_adds_the_operation_form_type_if_the_operation_is_set(
        FormEvent $event,
        FormInterface $form,
        AbstractMassEditOperator $operator,
        MassEditOperationInterface $operation
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn($operator);

        $operator->getOperation()->willReturn($operation);
        $operation->getFormType()->willReturn('foo_operation_type');
        $operation->getFormOptions()->willReturn(['foo' => 'bar']);

        $form->remove('operationAlias')->shouldBeCalled()->willReturn($form);
        $form->add('operation', 'foo_operation_type', ['foo' => 'bar'])->shouldBeCalled();

        $this->postSetData($event);
    }

    function it_does_nothing_when_no_data_is_set_on_the_form(
        FormEvent $event,
        FormInterface $form
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn(null);

        $form->remove(Argument::any())->shouldNotBeCalled();
        $form->add(Argument::cetera())->shouldNotBeCalled();
    }

    function it_does_nothing_if_the_operation_is_not_set_in_the_operator(
        FormEvent $event,
        FormInterface $form,
        AbstractMassEditOperator $operator
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn($operator);

        $operator->getOperation()->willReturn(null);

        $form->remove(Argument::any())->shouldNotBeCalled();
        $form->add(Argument::cetera())->shouldNotBeCalled();
    }
}
