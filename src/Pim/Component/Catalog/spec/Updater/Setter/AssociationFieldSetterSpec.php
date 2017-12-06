<?php

namespace spec\Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

class AssociationFieldSetterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductBuilderInterface $productBuilder
    ) {
        $this->beConstructedWith($productRepository, $productModelRepository, $groupRepository, $productBuilder, ['associations']);
    }

    function it_is_a_setter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\SetterInterface');
        $this->shouldImplement('Pim\Component\Catalog\Updater\Setter\FieldSetterInterface');
    }

    function it_supports_associations_field()
    {
        $this->supportsField('associations')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(false);
    }

    function it_checks_valid_association_data_format(ProductInterface $product)
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'associations',
                'Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter',
                'not an array'
            )
        )->during('setFieldData', [$product, 'associations', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "0".',
                'Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter',
                [0 => []]
            )
        )->during('setFieldData', [$product, 'associations', [0 => []]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                'Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter',
                ['assoc_type_code' => []]
            )
        )->during('setFieldData', [$product, 'associations', ['assoc_type_code' => []]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                'Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter',
                ['assoc_type_code' => ['products' => [1], 'groups' => [], 'product_models' => [],]]
            )
        )->during(
            'setFieldData',
            [$product, 'associations', ['assoc_type_code' => ['products' => [1], 'groups' => [], 'product_models' => []]]]
        );

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                'Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter',
                ['assoc_type_code' => ['products' => [], 'groups' => [2]]]
            )
        )->during(
            'setFieldData',
            [$product, 'associations', ['assoc_type_code' => ['products' => [], 'groups' => [2]]]]
        );

        $this->shouldThrow(
            new InvalidPropertyTypeException(
                'products',
                'string',
                'Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter',
                'Property "products" in association "assoc_type_code" expects an array as data, "string" given.',
                200
            )
        )->during(
            'setFieldData',
            [$product, 'associations', ['assoc_type_code' => ['products' => 'string', 'groups' => [], 'product_models' => []]]]
        );
    }

    function it_sets_association_field(
        $productRepository,
        $productModelRepository,
        $groupRepository,
        $productBuilder,
        ProductInterface $product,
        AssociationInterface $xsellAssociation,
        AssociationInterface $upsellAssociation,
        ProductInterface $assocProductOne,
        ProductInterface $assocProductTwo,
        ProductInterface $assocProductThree,
        ProductModelInterface $assocProductModelOne,
        ProductModelInterface $assocProductModelTwo,
        ProductModelInterface $assocProductModelThree,
        GroupInterface $assocGroupOne,
        GroupInterface $assocGroupTwo,
        AssociationTypeInterface $xsellAssociationType,
        AssociationTypeInterface $upsellAssociationType
    ) {
        $xsellAssociation->getAssociationType()->willReturn($xsellAssociationType);
        $xsellAssociation->getGroups()->willReturn(new ArrayCollection());
        $xsellAssociation->getProducts()->willReturn(new ArrayCollection());
        $xsellAssociation->getProductModels()->willReturn(new ArrayCollection());
        $upsellAssociation->getAssociationType()->willReturn($upsellAssociationType);
        $upsellAssociation->getGroups()->willReturn(new ArrayCollection());
        $upsellAssociation->getProducts()->willReturn(new ArrayCollection());
        $upsellAssociation->getProductModels()->willReturn(new ArrayCollection());

        $product->getAssociations()->willReturn(
            new ArrayCollection([$xsellAssociation->getWrappedObject(), $upsellAssociation->getWrappedObject()])
        );

        $productBuilder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('xsell')->willReturn($xsellAssociation);
        $product->getAssociationForTypeCode('upsell')->willReturn($upsellAssociation);

        $productRepository->findOneByIdentifier('assocProductOne')->willReturn($assocProductOne);
        $productRepository->findOneByIdentifier('assocProductTwo')->willReturn($assocProductTwo);
        $productRepository->findOneByIdentifier('assocProductThree')->willReturn($assocProductThree);

        $productModelRepository->findOneByIdentifier('assocProductModelOne')->willReturn($assocProductModelOne);
        $productModelRepository->findOneByIdentifier('assocProductModelTwo')->willReturn($assocProductModelTwo);
        $productModelRepository->findOneByIdentifier('assocProductModelThree')->willReturn($assocProductModelThree);

        $groupRepository->findOneByIdentifier('assocGroupOne')->willReturn($assocGroupOne);
        $groupRepository->findOneByIdentifier('assocGroupTwo')->willReturn($assocGroupTwo);

        $xsellAssociation->addProduct($assocProductOne)->shouldBeCalled();
        $xsellAssociation->addProduct($assocProductTwo)->shouldBeCalled();
        $xsellAssociation->addGroup($assocGroupOne)->shouldBeCalled();
        $xsellAssociation->addProductModel($assocProductModelOne)->shouldBeCalled();
        $xsellAssociation->addProductModel($assocProductModelTwo)->shouldBeCalled();

        $upsellAssociation->addProduct($assocProductThree)->shouldBeCalled();
        $upsellAssociation->addGroup($assocGroupTwo)->shouldBeCalled();
        $upsellAssociation->addProductModel($assocProductModelThree)->shouldBeCalled();

        $this->setFieldData(
            $product,
            'associations',
            [
                'xsell' => [
                    'products' => ['assocProductOne', 'assocProductTwo'],
                    'product_models' => ['assocProductModelOne', 'assocProductModelTwo'],
                    'groups' => ['assocGroupOne']
                ],
                'upsell' => [
                    'products' => ['assocProductThree'],
                    'product_models' => ['assocProductModelThree'],
                    'groups' => ['assocGroupTwo']
                ]
            ]
        );
    }

    function it_fails_if_one_of_the_association_type_code_does_not_exist(
        $productBuilder,
        ProductInterface $product
    ) {
        $product->getAssociations()->willReturn(new ArrayCollection());
        $productBuilder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('non valid association type code')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'association type code',
                'The association type does not exist',
                'Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter',
                'non valid association type code'
            )
        )->during(
            'setFieldData',
            [
                $product,
                'associations',
                ['non valid association type code' => ['groups' => [], 'products' => [], 'product_models' => []]]
            ]
        );
    }

    function it_fails_if_one_of_the_associated_product_does_not_exist(
        $productBuilder,
        $productRepository,
        ProductInterface $product,
        AssociationInterface $xsellAssociation,
        AssociationTypeInterface $associationType
    ) {
        $xsellAssociation->getAssociationType()->willReturn($associationType);
        $xsellAssociation->getGroups()->willReturn(new ArrayCollection());
        $xsellAssociation->getProducts()->willReturn(new ArrayCollection());

        $product->getAssociations()->willReturn(new ArrayCollection([$xsellAssociation->getWrappedObject()]));

        $productBuilder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('xsell')->willReturn($xsellAssociation);

        $productRepository->findOneByIdentifier('not existing product')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product identifier',
                'The product does not exist',
                'Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter',
                'not existing product'
            )
        )->during(
            'setFieldData',
            [
                $product,
                'associations',
                ['xsell' => ['groups' => [], 'products' => ['not existing product']]]
            ]
        );
    }

    function it_fails_if_one_of_the_associated_group_does_not_exist(
        $productBuilder,
        $groupRepository,
        ProductInterface $product,
        AssociationInterface $xsellAssociation,
        AssociationTypeInterface $associationType
    ) {
        $xsellAssociation->getAssociationType()->willReturn($associationType);
        $xsellAssociation->getGroups()->willReturn(new ArrayCollection([]));
        $xsellAssociation->getProducts()->willReturn(new ArrayCollection([]));
        $product->getAssociations()->willReturn(new ArrayCollection([$xsellAssociation->getWrappedObject()]));
        $productBuilder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('xsell')->willReturn($xsellAssociation);

        $groupRepository->findOneByIdentifier('not existing group')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'group code',
                'The group does not exist',
                'Pim\Component\Catalog\Updater\Setter\AssociationFieldSetter',
                'not existing group'
            )
        )->during(
            'setFieldData',
            [
                $product,
                'associations',
                ['xsell' => ['groups' => ['not existing group'], 'products' => [], 'product_models' => [],]]
            ]
        );
    }

    function it_should_clear_concerned_associations(
        $productBuilder,
        ProductInterface $product,
        AssociationInterface $xsellAssociation,
        AssociationInterface $upsellAssociation,
        AssociationTypeInterface $upsellAssociationType,
        AssociationTypeInterface $xsellAssociationType,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductModelInterface $productModel1,
        ProductModelInterface $productModel2,
        GroupInterface $group1,
        GroupInterface $group2
    ) {
        $xsellAssociationType->getCode()->willReturn('xsell');
        $xsellAssociation->getAssociationType()->willReturn($xsellAssociationType);
        $xsellAssociation->getProducts()->willReturn(new ArrayCollection([$product1->getWrappedObject(), $product2->getWrappedObject()]));
        $xsellAssociation->getProductModels()->willReturn(new ArrayCollection([$productModel1->getWrappedObject(), $productModel2->getWrappedObject()]));

        $upsellAssociationType->getCode()->willReturn('upsell');
        $upsellAssociation->getAssociationType()->willReturn($upsellAssociationType);
        $upsellAssociation->getGroups()->willReturn(new ArrayCollection([$group1->getWrappedObject(), $group2->getWrappedObject()]));
        $upsellAssociation->getProductModels()->willReturn(new ArrayCollection([$productModel1->getWrappedObject(), $productModel2->getWrappedObject()]));

        $product->getAssociations()->willReturn(
            new ArrayCollection([$xsellAssociation->getWrappedObject(), $upsellAssociation->getWrappedObject()])
        );

        $xsellAssociation->removeProduct($product1)->shouldBeCalled();
        $xsellAssociation->removeProduct($product2)->shouldBeCalled();
        $xsellAssociation->removeProductModel($productModel1)->shouldBeCalled();
        $xsellAssociation->removeProductModel($productModel2)->shouldBeCalled();

        $upsellAssociation->removeGroup($group1)->shouldBeCalled();
        $upsellAssociation->removeGroup($group2)->shouldBeCalled();
        $upsellAssociation->removeProductModel($productModel1)->shouldBeCalled();
        $upsellAssociation->removeProductModel($productModel2)->shouldBeCalled();

        $productBuilder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('xsell')->willReturn($xsellAssociation);
        $product->getAssociationForTypeCode('upsell')->willReturn($upsellAssociation);

        $this->setFieldData(
            $product,
            'associations',
            [
                'xsell' => [
                    'products' => [],
                    'product_models' => [],
                ],
                'upsell' => [
                    'groups' => [],
                    'product_models' => [],
                ]
            ]
        );
    }
}
