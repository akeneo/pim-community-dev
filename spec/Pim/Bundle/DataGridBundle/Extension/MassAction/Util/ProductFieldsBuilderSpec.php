<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Util;

use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManagerInterface;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class ProductFieldsBuilderSpec extends ObjectBehavior
{
    function let(
        ProductManagerInterface $productManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager,
        AssociationTypeManager $assocTypeManager,
        CatalogContext $catalogContext,
        ProductRepositoryInterface $productRepository,
        ObjectRepository $attributeRepository
    ) {
        $productManager->getProductRepository()->willReturn($productRepository);
        $productManager->getAttributeRepository()->willReturn($attributeRepository);

        $this->beConstructedWith(
            $productManager,
            $localeManager,
            $currencyManager,
            $assocTypeManager,
            $catalogContext
        );
    }

    function it_retrieves_field_list_with_empty_product_attributes_list($productRepository)
    {
        $productRepository->getAvailableAttributeIdsToExport(['foo'])->willReturn([]);

        $this->getFieldsList(['foo'])->shouldreturn([]);
    }

    function it_retrieves_field_list_with_product_with_attributes($productRepository, $attributeRepository, $assocTypeManager, AttributeInterface $bar, AttributeInterface $baz, AssociationTypeInterface $association)
    {
        $bar->getCode()->willReturn('bar-code');
        $bar->isLocalizable()->willReturn(false);
        $bar->isScopable()->willReturn(false);
        $bar->getAttributeType()->willReturn(null);

        $baz->getCode()->willReturn('baz-code');
        $baz->isLocalizable()->willReturn(false);
        $baz->isScopable()->willReturn(false);
        $baz->getAttributeType()->willReturn(null);

        $association->getCode()->willReturn('association-type-code');

        $assocTypeManager->getAssociationTypes()->willReturn([$association]);
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
