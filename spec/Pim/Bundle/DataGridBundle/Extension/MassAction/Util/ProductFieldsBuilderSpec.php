<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Util;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;

class ProductFieldsBuilderSpec extends ObjectBehavior
{
    function let(
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyManager $currencyManager,
        AssociationTypeRepositoryInterface $assocTypeRepo,
        CatalogContext $catalogContext,
        ProductRepositoryInterface $productRepository,
        ObjectRepository $attributeRepository
    ) {
        $this->beConstructedWith(
            $productRepository,
            $attributeRepository,
            $localeRepository,
            $currencyManager,
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
        $bar->getAttributeType()->willReturn(null);

        $baz->getCode()->willReturn('baz-code');
        $baz->isLocalizable()->willReturn(false);
        $baz->isScopable()->willReturn(false);
        $baz->getAttributeType()->willReturn(null);

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
