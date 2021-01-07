<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Prophecy\Argument;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductDraftChangesExtensionSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        RendererInterface $renderer,
        TranslatorInterface $translator,
        PresenterInterface $attributePresenter,
        PresenterInterface $valuePresenter,
        AttributeFactory $attributeFactory,
        ValueFactory $valueFactory
    ) {
        $this->beConstructedWith(
            $attributeRepository,
            $renderer,
            $translator,
            $valueFactory,
            $attributeFactory
        );

        $this->addPresenter($attributePresenter, 0);
        $this->addPresenter($valuePresenter, 1);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf(\Twig_Extension::class);
    }

    function it_has_presenters(
        $attributePresenter,
        $valuePresenter
    ) {
        $this->getPresenters()->shouldReturn([$valuePresenter, $attributePresenter]);
    }

    function it_presents_product_draft_using_a_supporting_presenter(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        $attributePresenter,
        $valuePresenter,
        ValueInterface $value,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft,
        AttributeInterface $attributeDescription
    ) {
        $productDraft->getEntityWithValue()->willReturn($product);
        $product->getValue('description', 'en_US', 'ecommerce')->willReturn($value);
        $value->getAttributeCode()->willReturn('description');
        $value->getData()->willReturn('data');
        $attributeRepository->findOneByIdentifier('description')->willReturn($attributeDescription);
        $attributeDescription->getType()->willReturn('pim_catalog_text');
        $attributeDescription->getReferenceDataName()->willReturn(null);

        $change = [
            'value' => 'foo',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
        ];
        $attributePresenter->supports('pim_catalog_text', Argument::any())->willReturn(false);
        $valuePresenter->supports('pim_catalog_text', Argument::any())->willReturn(true);
        $valuePresenter->present('data', array_merge($change, ['attribute' => 'description', 'reference_data_name' => null]))->willReturn('<b>changes</b>');

        $this->presentChange($productDraft, $change, 'description')->shouldReturn('<b>changes</b>');
    }

    function it_injects_translator_in_translator_aware_presenter(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        $translator,
        $attributePresenter,
        $valuePresenter,
        ValueInterface $value,
        ProductInterface $product,
        PresenterInterface $presenter,
        EntityWithValuesDraftInterface $productDraft,
        AttributeInterface $attributeDescription
    ) {
        $presenter->implement('Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TranslatorAwareInterface');
        $productDraft->getEntityWithValue()->willReturn($product);
        $product->getValue('description', 'en_US', 'ecommerce')->willReturn($value);
        $value->getAttributeCode()->willReturn('description');
        $value->getData()->willReturn('data');
        $attributeRepository->findOneByIdentifier('description')->willReturn($attributeDescription);
        $attributeDescription->getType()->willReturn('pim_catalog_text');
        $attributeDescription->getReferenceDataName()->willReturn(null);

        $change = [
            'attribute' => 'description',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'reference_data_name' => null,
        ];

        $attributePresenter->supports('pim_catalog_text', Argument::any())->willReturn(false);
        $valuePresenter->supports('pim_catalog_text', Argument::any())->willReturn(false);
        $presenter->supports('pim_catalog_text', Argument::any())->willReturn(true);
        $presenter->present('data', $change)->willReturn('<b>changes</b>');

        $presenter->setTranslator($translator)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
        $this->presentChange($productDraft, $change, 'description');
    }

    function it_injects_renderer_in_renderer_aware_presenter(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        $renderer,
        $attributePresenter,
        $valuePresenter,
        ValueInterface $value,
        ProductInterface $product,
        PresenterInterface $presenter,
        EntityWithValuesDraftInterface $productDraft,
        AttributeInterface $attributeDescription
    ) {
        $presenter->implement('Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\RendererAwareInterface');
        $productDraft->getEntityWithValue()->willReturn($product);
        $product->getValue('description', 'en_US', 'ecommerce')->willReturn($value);
        $value->getAttributeCode()->willReturn('description');
        $value->getData()->willReturn('data');
        $attributeRepository->findOneByIdentifier('description')->willReturn($attributeDescription);
        $attributeDescription->getType()->willReturn('pim_catalog_text');
        $attributeDescription->getReferenceDataName()->willReturn(null);

        $change = [
            'attribute' => 'description',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
            'reference_data_name' => null,
        ];

        $attributePresenter->supports('pim_catalog_text', Argument::any())->willReturn(false);
        $valuePresenter->supports('pim_catalog_text', Argument::any())->willReturn(false);
        $presenter->supports('pim_catalog_text', Argument::any())->willReturn(true);
        $presenter->present('data', $change)->willReturn('<b>changes</b>');

        $presenter->setRenderer($renderer)->shouldBeCalled();

        $this->addPresenter($presenter, 0);
        $this->presentChange($productDraft, $change, 'description');
    }
}
