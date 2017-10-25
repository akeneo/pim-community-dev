<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Handler;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GroupHandlerSpec extends ObjectBehavior
{
    function let(
        FormInterface $form,
        RequestStack $requestStack,
        SaverInterface $saver,
        ProductRepositoryInterface $repository,
        AttributeConverterInterface $localizedConverter
    ) {
        $this->beConstructedWith($form, $requestStack, $saver, $repository, $localizedConverter);
    }

    function it_is_a_handler()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface');
    }

    function it_saves_a_group_with_a_new_product_when_form_is_valid(
        $form,
        $requestStack,
        $saver,
        Request $request,
        GroupInterface $group,
        GroupTypeInterface $groupType,
        ProductInterface $product
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $form->setData($group)->shouldBeCalled();
        $request->isMethod('POST')->willReturn(true);
        $group->getProducts()->willReturn([$product]);
        $group->getType()->willReturn($groupType);

        $form->handleRequest($request)->shouldBeCalled();
        $form->isValid()->willReturn(true);

        $saver->save($group, ['copy_values_to_products' => true])->shouldBeCalled();

        $this->process($group)->shouldReturn(true);
    }

    function it_doesnt_save_a_group_when_form_is_not_valid(
        $form,
        $requestStack,
        $saver,
        Request $request,
        GroupInterface $group,
        GroupTypeInterface $groupType,
        ProductInterface $product
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $form->setData($group)->shouldBeCalled();
        $request->isMethod('POST')->willReturn(true);

        $group->getProducts()->willReturn([$product]);
        $form->handleRequest($request)->shouldBeCalled();

        $form->isValid()->willReturn(false);

        $group->getType()->willReturn($groupType);
        $saver->save($group)->shouldNotBeCalled();
        $this->process($group)->shouldReturn(false);
    }

    function it_doesnt_save_a_group_when_request_is_not_posted(
        $form,
        $requestStack,
        Request $request,
        GroupInterface $group
    ) {
        $requestStack->getCurrentRequest()->willReturn($request);
        $form->setData($group)->shouldBeCalled();
        $request->isMethod('POST')->willReturn(false);

        $this->process($group)->shouldReturn(false);
    }
}
