<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering\RendererInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
        $attributePresenter,
        $valuePresenter,
        ValueInterface $value,
        ProductInterface $product,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $productDraft->getEntityWithValue()->willReturn($product);
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
        ValueInterface $value,
        ProductInterface $product,
        PresenterInterface $presenter,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $presenter->implement('Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\TranslatorAwareInterface');
        $productDraft->getEntityWithValue()->willReturn($product);
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
        ValueInterface $value,
        ProductInterface $product,
        PresenterInterface $presenter,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $presenter->implement('Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\RendererAwareInterface');
        $productDraft->getEntityWithValue()->willReturn($product);
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
}
