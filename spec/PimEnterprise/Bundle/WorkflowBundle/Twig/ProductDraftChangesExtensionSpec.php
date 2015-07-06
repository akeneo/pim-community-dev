<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Twig;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ProductDraftChangesExtensionSpec extends ObjectBehavior
{
    function let(
        ObjectRepository $valueRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        RendererInterface $renderer,
        TranslatorInterface $translator,
        PresenterInterface $attributePresenter,
        PresenterInterface $valuePresenter,
        ProductManager $productManager,
        AttributeManager $attributeManager
    ) {
        $this->beConstructedWith(
            $valueRepository,
            $attributeRepository,
            $renderer,
            $translator,
            $productManager,
            $attributeManager
        );

        $this->addPresenter($attributePresenter, 0);
        $this->addPresenter($valuePresenter, 1);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_workflow_product_draft_changes_extension');
    }

    function it_has_presenters(
        $attributePresenter,
        $valuePresenter
    ) {
        $this->getPresenters()->shouldReturn([$valuePresenter, $attributePresenter]);
    }

    function it_presents_product_draft_change_attribute_using_a_supporting_presenter(
        $attributeRepository,
        $attributePresenter,
        Model\AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('foo')->willReturn($attribute);

        $attributePresenter->supports($attribute)->willReturn(true);
        $attributePresenter->present($attribute, ['attribute' => 'description'])->willReturn('Name');

        $this->presentAttribute(['attribute' => 'description'], 'foo')->shouldReturn('Name');
    }

    function it_presents_product_draft_change_attribute_using_the_default_value_if_id_is_unavailable()
    {
        $this->presentAttribute([], 'foo')->shouldReturn('foo');
    }

    function it_presents_product_draft_change_attribute_using_the_default_value_if_value_is_unavailable(
        $valueRepository
    ) {
        $valueRepository->find(123)->willReturn(null);

        $this->presentAttribute(['attribute_id' => '123'], 'foo')->shouldReturn('foo');
    }

    function it_presents_product_draft_using_a_supporting_presenter(
        $attributePresenter,
        $valuePresenter,
        Model\ProductValueInterface $value,
        Model\ProductInterface $product,
        ProductDraftInterface $productDraft
    ) {
        $productDraft->getProduct()->willReturn($product);
        $product->getValue('description', 'en_US', 'ecommerce')->willReturn($value);

        $change = [
            'value' => 'foo',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
        ];
        $attributePresenter->supports($value)->willReturn(false);
        $valuePresenter->supports($value)->willReturn(true);
        $valuePresenter->present($value, $change)->willReturn('<b>changes</b>');

        $this->presentChange($productDraft, $change, 'description')->shouldReturn('<b>changes</b>');
    }

    function it_injects_translator_in_translator_aware_presenter(
        $translator,
        $attributePresenter,
        $valuePresenter,
        Model\ProductValueInterface $value,
        Model\ProductInterface $product,
        PresenterInterface $presenter,
        ProductDraftInterface $productDraft
    ) {
        $presenter->implement('PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface');
        $productDraft->getProduct()->willReturn($product);
        $product->getValue('description', 'en_US', 'ecommerce')->willReturn($value);
        $change = [
            'attribute' => 'description',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
        ];

        $attributePresenter->supports($value)->willReturn(false);
        $valuePresenter->supports($value)->willReturn(false);
        $presenter->supports($value)->willReturn(true);
        $presenter->present($value, $change)->willReturn('<b>changes</b>');

        $presenter->setTranslator($translator)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
        $this->presentChange($productDraft, $change, 'description');
    }

    function it_injects_renderer_in_renderer_aware_presenter(
        $renderer,
        $attributePresenter,
        $valuePresenter,
        Model\ProductValueInterface $value,
        Model\ProductInterface $product,
        PresenterInterface $presenter,
        ProductDraftInterface $productDraft
    ) {
        $presenter->implement('PimEnterprise\Bundle\WorkflowBundle\Presenter\RendererAwareInterface');
        $productDraft->getProduct()->willReturn($product);
        $product->getValue('description', 'en_US', 'ecommerce')->willReturn($value);
        $change = [
            'attribute' => 'description',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
        ];

        $attributePresenter->supports($value)->willReturn(false);
        $valuePresenter->supports($value)->willReturn(false);
        $presenter->supports($value)->willReturn(true);
        $presenter->present($value, $change)->willReturn('<b>changes</b>');

        $presenter->setRenderer($renderer)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
        $this->presentChange($productDraft, $change, 'description');
    }

    function it_injects_twig_in_twig_aware_presenter(
        $attributePresenter,
        $valuePresenter,
        Model\ProductValueInterface $value,
        Model\ProductInterface $product,
        PresenterInterface $presenter,
        \Twig_Environment $twig,
        ProductDraftInterface $productDraft
    ) {
        $presenter->implement('PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAwareInterface');
        $productDraft->getProduct()->willReturn($product);
        $product->getValue('description', 'en_US', 'ecommerce')->willReturn($value);
        $change = [
            'attribute' => 'description',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
        ];

        $attributePresenter->supports($value)->willReturn(false);
        $valuePresenter->supports($value)->willReturn(false);
        $presenter->supports($value)->willReturn(true);
        $presenter->present($value, $change)->willReturn('<b>changes</b>');

        $presenter->setTwig($twig)->shouldBeCalled();

        $this->initRuntime($twig);
        $this->addPresenter($presenter, 0);
        $this->presentChange($productDraft, $change, 'description');
    }
}
