<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Util;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;

class ProductFieldsBuilderSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepo,
        CatalogContext $catalogContext
    ) {
        $this->beConstructedWith(
            $productRepository,
            $attributeRepository,
            $localeRepository,
            $currencyRepository,
            $assocTypeRepo,
            $catalogContext
        );
    }

    function it_retrieves_field_list_with_empty_product_attributes_list($productRepository)
    {
        $productRepository->getAvailableAttributeIdsToExport(['foo'])->willReturn([]);

        $this->getFieldsList(['foo'])->shouldreturn([]);
    }

    function it_retrieves_field_list_with_product_with_attributes(
        $productRepository,
        $attributeRepository,
        $assocTypeRepo,
        AttributeInterface $bar,
        AttributeInterface $baz,
        AssociationTypeInterface $association
    ) {
        $bar->getCode()->willReturn('bar-code');
        $bar->isLocalizable()->willReturn(false);
        $bar->isScopable()->willReturn(false);
        $bar->getType()->willReturn(null);

        $baz->getCode()->willReturn('baz-code');
        $baz->isLocalizable()->willReturn(false);
        $baz->isScopable()->willReturn(false);
        $baz->getType()->willReturn(null);

        $association->getCode()->willReturn('association-type-code');

        $assocTypeRepo->findAll()->willReturn([$association]);
        $productRepository->getAvailableAttributeIdsToExport(['foo'])->willReturn(['bar', 'baz']);
        $attributeRepository->findBy(['id' => ['bar', 'baz']])->willReturn([$bar, $baz]);

        $this->getFieldsList(['foo'])->shouldReturn([
            "bar-code",
            "baz-code",
            "family",
            "categories",
            "groups",
            "association-type-code-groups",
            "association-type-code-products",
        ]);
    }
}
