<?php

namespace spec\Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

class AssociationFieldAdderSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductBuilderInterface $productBuilder
    ) {
        $this->beConstructedWith($productRepository, $productModelRepository, $groupRepository, $productBuilder, ['associations']);
    }

    function it_is_an_adder()
    {
        $this->shouldImplement('Pim\Component\Catalog\Updater\Adder\AdderInterface');
        $this->shouldImplement('Pim\Component\Catalog\Updater\Adder\FieldAdderInterface');
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
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                'not an array'
            )
        )->during('addFieldData', [$product, 'associations', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "0".',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                [0 => []]
            )
        )->during('addFieldData', [$product, 'associations', [0 => []]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                ['assoc_type_code' => []]
            )
        )->during('addFieldData', [$product, 'associations', ['assoc_type_code' => []]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                ['assoc_type_code' => ['products' => []]]
            )
        )->during('addFieldData', [$product, 'associations', ['assoc_type_code' => ['products' => []]]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                ['assoc_type_code' => ['groups' => []]]
            )
        )->during('addFieldData', [$product, 'associations', ['assoc_type_code' => ['groups' => []]]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                ['assoc_type_code' => ['products' => [1], 'groups' => [], 'product_models' => [],]]
            )
        )->during(
            'addFieldData',
            [$product, 'associations', ['assoc_type_code' => ['products' => [1], 'groups' => [], 'product_models' => [],]]]
        );

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                ['assoc_type_code' => ['products' => [], 'groups' => [2], 'product_models' => [],]]
            )
        )->during(
            'addFieldData',
            [$product, 'associations', ['assoc_type_code' => ['products' => [], 'groups' => [2], 'product_models' => [],]]]
        );

        $this->shouldThrow(
            new InvalidPropertyTypeException(
                'products',
                'string',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                'Property "products" in association "assoc_type_code" expects an array as data, "string" given.',
                200
            )
        )->during(
            'addFieldData',
            [$product, 'associations', ['assoc_type_code' => ['products' => 'string', 'groups' => []]]]
        );
    }

    function it_adds_association_field(
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
        GroupInterface $assocGroupTwo
    ) {
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
        $upsellAssociation->addProductModel($assocProductModelThree)->shouldBeCalled();
        $upsellAssociation->addGroup($assocGroupTwo)->shouldBeCalled();

        $this->addFieldData(
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
        $product->getAssociations()->willReturn([]);
        $productBuilder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('non valid association type code')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'association type code',
                'The association type does not exist',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                'non valid association type code'
            )
        )->during(
            'addFieldData',
            [
                $product,
                'associations',
                ['non valid association type code' => ['groups' => [], 'products' => []]]
            ]
        );
    }

    function it_fails_if_one_of_the_associated_product_does_not_exist(
        $productBuilder,
        $productRepository,
        ProductInterface $product,
        AssociationInterface $xsellAssociation
    ) {
        $product->getAssociations()->willReturn([$xsellAssociation]);
        $productBuilder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('xsell')->willReturn($xsellAssociation);

        $productRepository->findOneByIdentifier('not existing product')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product identifier',
                'The product does not exist',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                'not existing product'
            )
        )->during(
            'addFieldData',
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
        AssociationInterface $xsellAssociation
    ) {
        $product->getAssociations()->willReturn([$xsellAssociation]);
        $productBuilder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('xsell')->willReturn($xsellAssociation);

        $groupRepository->findOneByIdentifier('not existing group')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'group code',
                'The group does not exist',
                'Pim\Component\Catalog\Updater\Adder\AssociationFieldAdder',
                'not existing group'
            )
        )->during(
            'addFieldData',
            [
                $product,
                'associations',
                ['xsell' => ['groups' => ['not existing group'], 'products' => []]]
            ]
        );
    }
}
