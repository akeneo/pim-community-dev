<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Reader\CachedReader;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Pim\Bundle\TransformBundle\Cache\AttributeCache;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfo;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\DefaultTransformer;
use Pim\Bundle\TransformBundle\Transformer\Property\RelationTransformer;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ProductTransformerSpec extends ObjectBehavior
{
    const PRODUCT_CLASS = 'Pim\Bundle\CatalogBundle\Model\Product';
    const VALUE_CLASS   = 'Pim\Bundle\CatalogBundle\Model\ProductValue';

    function let(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        AttributeCache $attributeCache,
        CachedReader $associationReader,
        ProductTemplateUpdaterInterface $templateUpdater,
        ProductBuilderInterface $productBuilder,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith(
            $doctrine,
            $propertyAccessor,
            $guesser,
            $columnInfoTransformer,
            $attributeCache,
            $associationReader,
            $templateUpdater,
            $productBuilder,
            $productRepository,
            self::PRODUCT_CLASS,
            self::VALUE_CLASS
        );
    }

    function it_is_an__entity_transformer()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\EntityTransformer');
    }

    function it_transform_an_existing_product(
        $guesser,
        $columnInfoTransformer,
        $attributeCache,
        $doctrine,
        $associationReader,
        $templateUpdater,
        $productRepository,
        AttributeInterface $attributeSku,
        AttributeInterface $attributeDesc,
        ColumnInfo $columnInfoSku,
        ColumnInfo $columnInfoFamily,
        ColumnInfo $columnInfoGroups,
        ColumnInfo $columnInfoCategories,
        ColumnInfo $columnInfoAssocGroups,
        ColumnInfo $columnInfoAssocProducts,
        ColumnInfo $columnInfoDesc,
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductInterface $product,
        ObjectManager $objectManager,
        ClassMetadata $productMetadata,
        RelationTransformer $relationFamily,
        RelationTransformer $relationGroups,
        RelationTransformer $relationCategories,
        ClassMetadata $productValueMetadata,
        DefaultTransformer $defaultTransformer,
        ProductValueInterface $valueSku,
        ProductValueInterface $valueDesc,
        GroupInterface $variantGroup,
        ProductTemplateInterface $productTemplate
    ) {
        // initialize attributes
        $columnInfoTransformer
            ->transform(
                'Pim\Bundle\CatalogBundle\Model\Product',
                [
                    'sku',
                    'family',
                    'groups',
                    'categories',
                    'SUBSTITUTION-groups',
                    'SUBSTITUTION-products',
                    'description-en_US-mobile'
                ]
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $columnInfoSku,
                    $columnInfoFamily,
                    $columnInfoGroups,
                    $columnInfoCategories,
                    $columnInfoAssocGroups,
                    $columnInfoAssocProducts,
                    $columnInfoDesc
                ]
            );

        $columnInfoSku->getName()->willReturn('sku');
        $columnInfoSku->getPropertyPath()->willReturn('sku');
        $columnInfoSku->getLabel()->willReturn('sku');
        $columnInfoSku->getSuffixes()->willReturn([]);

        $columnInfoFamily->getName()->willReturn('family');
        $columnInfoFamily->getPropertyPath()->willReturn('family');
        $columnInfoFamily->getLabel()->willReturn('family');
        $columnInfoFamily->getSuffixes()->willReturn([]);

        // TODO : we should introduce a variant_group column after PIM-2458
        $columnInfoGroups->getName()->willReturn('groups');
        $columnInfoGroups->getPropertyPath()->willReturn('groups');
        $columnInfoGroups->getLabel()->willReturn('groups');
        $columnInfoGroups->getSuffixes()->willReturn([]);

        $columnInfoCategories->getName()->willReturn('categories');
        $columnInfoCategories->getPropertyPath()->willReturn('categories');
        $columnInfoCategories->getLabel()->willReturn('categories');
        $columnInfoCategories->getSuffixes()->willReturn([]);

        $columnInfoAssocGroups->getName()->willReturn('SUBSTITUTION');
        $columnInfoAssocGroups->getPropertyPath()->willReturn('sUBSTITUTION');
        $columnInfoAssocGroups->getLabel()->willReturn('SUBSTITUTION-groups');
        $columnInfoAssocGroups->getSuffixes()->willReturn(['groups']);

        $columnInfoAssocProducts->getName()->willReturn('SUBSTITUTION');
        $columnInfoAssocProducts->getPropertyPath()->willReturn('sUBSTITUTION');
        $columnInfoAssocProducts->getLabel()->willReturn('SUBSTITUTION-products');
        $columnInfoAssocProducts->getSuffixes()->willReturn(['products']);

        $columnInfoDesc->getName()->willReturn('description');
        $columnInfoDesc->getPropertyPath()->willReturn('description');
        $columnInfoDesc->getLabel()->willReturn('description');
        $columnInfoDesc->getSuffixes()->willReturn([]);

        $attributeCache
            ->getAttributes(
                [
                    $columnInfoSku,
                    $columnInfoFamily,
                    $columnInfoGroups,
                    $columnInfoCategories,
                    $columnInfoAssocGroups,
                    $columnInfoAssocProducts,
                    $columnInfoDesc
                ]
            )
            ->willReturn(
                [
                    'sku' => $attributeSku,
                    'description' => $attributeDesc
                ]
            );

        $columnInfoSku->setAttribute($attributeSku)->shouldBeCalled();
        $attributeSku->getAttributeType()->willReturn('pim_catalog_identifier');
        $columnInfoFamily->setAttribute(null)->shouldBeCalled();
        $columnInfoGroups->setAttribute(null)->shouldBeCalled();
        $columnInfoCategories->setAttribute(null)->shouldBeCalled();
        $columnInfoDesc->setAttribute($attributeDesc)->shouldBeCalled();

        // find entity
        $attributeSku->getCode()->willReturn('sku');
        $productRepository->findOneByIdentifier('AKNTS')->willReturn($product);

        // set product properties
        $doctrine->getManagerForClass('Pim\Bundle\CatalogBundle\Model\Product')
            ->willReturn($objectManager);
        $objectManager->getClassMetadata('Pim\Bundle\CatalogBundle\Model\Product')
            ->willReturn($productMetadata);

        $guesser->getTransformerInfo($columnInfoFamily, $productMetadata)
            ->willReturn(
                [
                    $relationFamily,
                    [
                        'class' => 'Pim\Bundle\CatalogBundle\Entity\Family',
                        'multiple' => false
                    ]
                ]
            );
        $guesser->getTransformerInfo($columnInfoGroups, $productMetadata)
            ->willReturn(
                [
                    $relationGroups,
                    [
                        'class' => 'Pim\Bundle\CatalogBundle\Entity\Group',
                        'multiple' => true
                    ]
                ]
            );
        $guesser->getTransformerInfo($columnInfoCategories, $productMetadata)
            ->willReturn(
                [
                    $relationCategories,
                    [
                        'class' => 'Pim\Bundle\CatalogBundle\Entity\Category',
                        'multiple' => true
                    ]
                ]
            );

        // set product values
        $attributeCache->getRequiredAttributeCodes($product)
            ->willReturn(['sku', 'description']);
        $doctrine->getManagerForClass('Pim\Bundle\CatalogBundle\Model\ProductValue')
            ->willReturn($objectManager);
        $objectManager->getClassMetadata('Pim\Bundle\CatalogBundle\Model\ProductValue')
            ->willReturn($productValueMetadata);

        $guesser->getTransformerInfo($columnInfoSku, $productValueMetadata)
            ->willReturn(
                [
                    $defaultTransformer,
                    []
                ]
            );

        $guesser->getTransformerInfo($columnInfoDesc, $productValueMetadata)
            ->willReturn(
                [
                    $defaultTransformer,
                    []
                ]
            );

        $columnInfoSku->getLocale()->willReturn(null);
        $columnInfoSku->getScope()->willReturn(null);

        $columnInfoDesc->getLocale()->willReturn('en_US');
        $columnInfoDesc->getScope()->willReturn('mobile');

        $product->getValue('sku', null, null)->willReturn($valueSku);
        $product->getValue('description', 'de_DE', 'mobile')->willReturn($valueDesc);

        // set variant group values
        $product->getVariantGroup()->willReturn($variantGroup);
        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $templateUpdater->update($productTemplate, [$product])->shouldBeCalled();

        // set associations
        $product->getReference()->willReturn('AKNTS');
        $associationReader
            ->addItem(
                [
                    'association_type' => 'SUBSTITUTION',
                    'owner' => 'AKNTS',
                    'products' => 'AKNTS_WPS,AKNTS_PBS,AKNTS_PWS'
                ]
            )
            ->shouldBeCalled();

        $this->transform(
            'Pim\Bundle\CatalogBundle\Model\Product',
            [
                'sku' => 'AKNTS',
                'family' => 'tshirts',
                'groups' => 'akeneo_tshirt',
                'categories' => 'tshirts,goodies',
                'SUBSTITUTION-groups' => '',
                'SUBSTITUTION-products' => 'AKNTS_WPS,AKNTS_PBS,AKNTS_PWS',
                'description-en_US-mobile' => '<p>Akeneo T-Shirt</p>'
            ]
        );
    }
}
