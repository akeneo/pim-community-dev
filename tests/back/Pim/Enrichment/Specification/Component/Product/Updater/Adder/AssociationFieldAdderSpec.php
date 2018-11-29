<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AssociationFieldAdder;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

class AssociationFieldAdderSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder
    ) {
        $this->beConstructedWith($productRepository, $productModelRepository, $groupRepository, $missingAssociationAdder, ['associations']);
    }

    function it_is_an_adder()
    {
        $this->shouldImplement(AdderInterface::class);
        $this->shouldImplement(FieldAdderInterface::class);
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
                AssociationFieldAdder::class,
                'not an array'
            )
        )->during('addFieldData', [$product, 'associations', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "0".',
                AssociationFieldAdder::class,
                [0 => []]
            )
        )->during('addFieldData', [$product, 'associations', [0 => []]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                AssociationFieldAdder::class,
                ['assoc_type_code' => []]
            )
        )->during('addFieldData', [$product, 'associations', ['assoc_type_code' => []]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                AssociationFieldAdder::class,
                ['assoc_type_code' => ['products' => []]]
            )
        )->during('addFieldData', [$product, 'associations', ['assoc_type_code' => ['products' => []]]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                AssociationFieldAdder::class,
                ['assoc_type_code' => ['groups' => []]]
            )
        )->during('addFieldData', [$product, 'associations', ['assoc_type_code' => ['groups' => []]]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                AssociationFieldAdder::class,
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
                AssociationFieldAdder::class,
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
                AssociationFieldAdder::class,
                'Property "products" in association "assoc_type_code" expects an array as data, "string" given.',
                200
            )
        )->during(
            'addFieldData',
            [$product, 'associations', ['assoc_type_code' => ['products' => 'string', 'groups' => [], 'product_models' => []]]]
        );
    }

    function it_adds_association_field(
        $productRepository,
        $productModelRepository,
        $groupRepository,
        $missingAssociationAdder,
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
        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();

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

    function it_adds_association_field_even_when_the_association_type_code_is_a_string_representing_an_integer(
        $productRepository,
        $groupRepository,
        $missingAssociationAdder,
        ProductInterface $product,
        AssociationInterface $assoc666,
        ProductInterface $assocProductOne,
        ProductInterface $assocProductTwo,
        GroupInterface $assocGroupOne,
        GroupInterface $assocGroupTwo
    ) {
        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('666')->willReturn($assoc666);

        $productRepository->findOneByIdentifier('assocProductOne')->willReturn($assocProductOne);
        $productRepository->findOneByIdentifier('assocProductTwo')->willReturn($assocProductTwo);

        $groupRepository->findOneByIdentifier('assocGroupOne')->willReturn($assocGroupOne);
        $groupRepository->findOneByIdentifier('assocGroupTwo')->willReturn($assocGroupTwo);

        $assoc666->addProduct($assocProductOne)->shouldBeCalled();
        $assoc666->addProduct($assocProductTwo)->shouldBeCalled();
        $assoc666->addGroup($assocGroupOne)->shouldBeCalled();

        $this->addFieldData(
            $product,
            'associations',
            [
                '666' => [
                    'products' => ['assocProductOne', 'assocProductTwo'],
                    'groups' => ['assocGroupOne'],
                    'product_models' => [],
                ],
            ]
        );
    }

    function it_fails_if_one_of_the_association_type_code_does_not_exist(
        $missingAssociationAdder,
        ProductInterface $product
    ) {
        $product->getAssociations()->willReturn([]);
        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('non valid association type code')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'association type code',
                'The association type does not exist',
                AssociationFieldAdder::class,
                'non valid association type code'
            )
        )->during(
            'addFieldData',
            [
                $product,
                'associations',
                ['non valid association type code' => ['groups' => [], 'products' => [], 'product_models' => []]]
            ]
        );
    }

    function it_fails_if_one_of_the_associated_product_does_not_exist(
        $missingAssociationAdder,
        $productRepository,
        ProductInterface $product,
        AssociationInterface $xsellAssociation
    ) {
        $product->getAssociations()->willReturn([$xsellAssociation]);
        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('xsell')->willReturn($xsellAssociation);

        $productRepository->findOneByIdentifier('not existing product')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product identifier',
                'The product does not exist',
                AssociationFieldAdder::class,
                'not existing product'
            )
        )->during(
            'addFieldData',
            [
                $product,
                'associations',
                ['xsell' => ['groups' => [], 'products' => ['not existing product'], 'product_models' => []]]
            ]
        );
    }

    function it_fails_if_one_of_the_associated_group_does_not_exist(
        $missingAssociationAdder,
        $groupRepository,
        ProductInterface $product,
        AssociationInterface $xsellAssociation
    ) {
        $product->getAssociations()->willReturn([$xsellAssociation]);
        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociationForTypeCode('xsell')->willReturn($xsellAssociation);

        $groupRepository->findOneByIdentifier('not existing group')->willReturn(null);

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'group code',
                'The group does not exist',
                AssociationFieldAdder::class,
                'not existing group'
            )
        )->during(
            'addFieldData',
            [
                $product,
                'associations',
                ['xsell' => ['groups' => ['not existing group'], 'products' => [], 'product_models' => []]]
            ]
        );
    }
}
