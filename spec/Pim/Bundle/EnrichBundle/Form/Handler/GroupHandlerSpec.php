<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Handler;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Prophecy\Argument;
use Pim\Component\Resource\Model\SaverInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupHandlerSpec extends ObjectBehavior
{
    function let(FormInterface $form, Request $request, SaverInterface $saver, ProductRepositoryInterface $repository)
    {
        $this->beConstructedWith($form, $request, $saver, $repository);
    }

    function it_is_a_handler()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface');
    }

    function it_saves_a_group_with_a_new_product_when_form_is_valid(
        $form,
        $request,
        $saver,
        GroupInterface $group,
        ProductInterface $product,
        ProductInterface $addedProduct,
        FormInterface $addedForm,
        FormInterface $removedForm
    ) {
        $form->setData($group)->shouldBeCalled();
        $request->isMethod('POST')->willReturn(true);
        $group->getProducts()->willReturn([$product]);

        $form->submit($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $form->get('appendProducts')->willReturn($addedForm);
        $form->get('removeProducts')->willReturn($removedForm);
        $addedForm->getData()->willReturn([$addedProduct]);
        $removedForm->getData()->willReturn([]);

        $saver->save($group, ['append_products' => [$addedProduct], 'remove_products' => []])->shouldBeCalled();

        $this->process($group)->shouldReturn(true);
    }

    function it_doesnt_save_a_group_when_form_is_not_valid($form, $request, $saver, GroupInterface $group, ProductInterface $product)
    {
        $form->setData($group)->shouldBeCalled();
        $request->isMethod('POST')->willReturn(true);
        $group->getProducts()->willReturn([$product]);
        $form->submit($request)->shouldBeCalled();
        $form->isValid()->willReturn(false);
        $saver->save($group)->shouldNotBeCalled();

        $this->process($group)->shouldReturn(false);
    }

    function it_doesnt_save_a_group_when_request_is_not_posted($form, $request, $saver, GroupInterface $group)
    {
        $form->setData($group)->shouldBeCalled();
        $request->isMethod('POST')->willReturn(false);

        $this->process($group)->shouldReturn(false);
    }
}
