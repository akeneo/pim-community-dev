<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Twig;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\AttributeFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Prophecy\Argument;
use Symfony\Component\Translation\TranslatorInterface;

class ProductDraftChangesExtensionSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        RendererInterface $renderer,
        TranslatorInterface $translator,
        ProductBuilderInterface $productBuilder,
        AttributeFactory $attributeFactory
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $renderer,
            $translator,
            $productBuilder,
            $attributeFactory
        );
    }

    function it_has_filters()
    {
        $filters = $this->getFilters();

        $filters->shouldHaveCount(2);
        $filters[0]->shouldBeAnInstanceOf('\Twig_SimpleFilter');
        $filters[1]->shouldBeAnInstanceOf('\Twig_SimpleFilter');
        $filters[0]->getName()->shouldReturn('present_new_change');
        $filters[1]->getName()->shouldReturn('present_original_change');
    }

    function it_has_functions()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(1);
        $functions[0]->shouldBeAnInstanceOf('\Twig_SimpleFunction');
        $functions[0]->getName()->shouldReturn('get_attribute_label_from_code');
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf('\Twig_Extension');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee_workflow_product_draft_changes_extension');
    }

    function it_injects_twig_in_presenters_at_runtime(\Twig_Environment $twig, PresenterInterface $presenter)
    {
        $presenter->implement('PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAwareInterface');
        $presenter->setTwig($twig)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
        $this->initRuntime($twig);
    }

    function it_injects_twig_in_presenters_at_compilation(\Twig_Environment $twig, PresenterInterface $presenter)
    {
        $presenter->implement('PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAwareInterface');
        $presenter->setTwig($twig)->shouldBeCalled();

        $this->initRuntime($twig);
        $this->addPresenter($presenter, 0);
    }

    function it_injects_renderer_in_presenters_at_compilation($renderer, PresenterInterface $presenter)
    {
        $presenter->implement('PimEnterprise\Bundle\WorkflowBundle\Presenter\RendererAwareInterface');
        $presenter->setRenderer($renderer)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
    }

    function it_injects_translator_in_presenters_at_compilation($translator, PresenterInterface $presenter)
    {
        $presenter->implement('PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface');
        $presenter->setTranslator($translator)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
    }

    function it_present_new_changes(
        $attributeRepository,
        $productBuilder,
        $attributeFactory,
        AttributeInterface $attribute,
        AttributeInterface $newAttribute,
        ProductValueInterface $value,
        PresenterInterface $supportedPresenter,
        PresenterInterface $unsupportedPresenter
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('a type');
        $attribute->getReferenceDataName()->willReturn('a reference data name');
        $attributeFactory->createAttribute('a type')->willReturn($newAttribute);
        $productBuilder->createProductValue($newAttribute)->willReturn($value);
        $newAttribute->setReferenceDataName('a reference data name')->shouldBeCalled();
        $value->setAttribute($newAttribute)->shouldBeCalled();

        $supportedPresenter->supports($value)->willReturn(true);
        $supportedPresenter->presentNew($value, ['data' => 'Some T-Shirt'])->willReturn('expected result');

        $unsupportedPresenter->supports($value)->willReturn(false);
        $unsupportedPresenter->presentNew(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->addPresenter($supportedPresenter, 1);
        $this->addPresenter($unsupportedPresenter, 0);

        $this->presentNewChange(['data' => 'Some T-Shirt'], 'name')->shouldReturn('expected result');
    }

    function it_present_new_changes_without_supported_presenter(
        $attributeRepository,
        $productBuilder,
        $attributeFactory,
        AttributeInterface $attribute,
        AttributeInterface $newAttribute,
        ProductValueInterface $value
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('a type');
        $attribute->getReferenceDataName()->willReturn('a reference data name');
        $attributeFactory->createAttribute('a type')->willReturn($newAttribute);
        $productBuilder->createProductValue($newAttribute)->willReturn($value);
        $newAttribute->setReferenceDataName('a reference data name')->shouldBeCalled();
        $value->setAttribute($newAttribute)->shouldBeCalled();

        $this->presentNewChange(['data' => 'Some T-Shirt'], 'name')->shouldReturn('');
    }

    function it_present_original_changes(
        ProductValueInterface $value,
        PresenterInterface $supportedPresenter,
        PresenterInterface $unsupportedPresenter
    ) {
        $supportedPresenter->supports($value)->willReturn(true);
        $supportedPresenter->presentOriginal($value, ['data' => 'Some T-Shirt'])->willReturn('expected result');

        $unsupportedPresenter->supports($value)->willReturn(false);
        $unsupportedPresenter->presentOriginal(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->addPresenter($supportedPresenter, 1);
        $this->addPresenter($unsupportedPresenter, 0);

        $this->presentOriginalChange($value, ['data' => 'Some T-Shirt'])->shouldReturn('expected result');
    }

    function it_present_original_changes_without_supported_presenter(ProductValueInterface $value)
    {
        $this->presentOriginalChange($value)->shouldReturn('');
    }

    function it_present_original_changes_with_null_value()
    {
        $this->presentOriginalChange(null)->shouldReturn('');
    }
}
