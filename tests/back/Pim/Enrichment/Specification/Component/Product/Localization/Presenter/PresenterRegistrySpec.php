<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

class PresenterRegistrySpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_a_localizer_registry()
    {
        $this->shouldImplement(PresenterRegistryInterface::class);
    }

    function it_get_localizer(
        $attributeRepository,
        PresenterInterface $presenter,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('number')->willReturn($attribute);
        $attribute->getType()->willReturn('pim_catalog_number');
        $presenter->supports('pim_catalog_number')->willReturn(true);
        $this->register($presenter, 'product_value');
        $this->getPresenterByAttributeCode('number')->shouldReturn($presenter);
    }

    function it_returns_null_if_there_is_no_localizer(
        $attributeRepository,
        PresenterInterface $presenter,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('number')->willReturn($attribute);
        $attribute->getType()->willReturn('pim_catalog_number');
        $presenter->supports('pim_catalog_number')->willReturn(false);
        $this->register($presenter, 'product_value');
        $this->getPresenterByAttributeCode('number')->shouldReturn(null);
    }

    function it_get_product_value_localizer(PresenterInterface $presenter)
    {
        $presenter->supports('pim_catalog_number')->willReturn(true);
        $this->register($presenter, 'attribute_option');
        $this->getAttributeOptionPresenter('pim_catalog_number')->shouldReturn($presenter);
    }

    function it_returns_null_if_there_is_no_product_value_localizer(PresenterInterface $presenter)
    {
        $presenter->supports('pim_catalog_number')->willReturn(false);
        $this->register($presenter, 'attribute_option');
        $this->getAttributeOptionPresenter('pim_catalog_number')->shouldReturn(null);
    }
}
