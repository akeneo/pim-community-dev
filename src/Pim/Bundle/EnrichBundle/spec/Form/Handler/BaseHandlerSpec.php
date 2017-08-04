<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Handler;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class BaseHandlerSpec extends ObjectBehavior
{
    function let(FormInterface $form, RequestStack $requestStack, SaverInterface $saver)
    {
        $this->beConstructedWith($form, $requestStack, $saver);
    }

    function it_is_a_handler()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface');
    }

    function it_saves_an_entity_when_form_is_valid(
        $form, $requestStack,
        $saver,
        AttributeInterface $entity,
        Request $request
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $form->setData($entity)->shouldBeCalled();
        $request->isMethod('POST')->willReturn(true);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);
        $saver->save($entity)->shouldBeCalled();

        $this->process($entity)->shouldReturn(true);
    }

    function it_doesnt_save_an_entity_when_form_is_not_valid(
        $form,
        $requestStack,
        $saver,
        AttributeInterface $entity,
        Request $request
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $form->setData($entity)->shouldBeCalled();
        $request->isMethod('POST')->willReturn(true);
        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(false);
        $saver->save($entity)->shouldNotBeCalled();

        $this->process($entity)->shouldReturn(false);
    }

    function it_doesnt_save_an_entity_when_request_is_not_posted(
        $form,
        $requestStack,
        $saver,
        AttributeInterface $entity,
        Request $request
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $form->setData($entity)->shouldBeCalled();
        $request->isMethod('POST')->willReturn(false);

        $this->process($entity)->shouldReturn(false);
    }
}
