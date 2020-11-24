<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AssociationFieldAdder;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

class AssociationFieldAdderSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder
    ) {
        $this->beConstructedWith(
            $productRepository,
            $productModelRepository,
            $groupRepository,
            $missingAssociationAdder,
            ['associations']
        );
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

    function it_adds_product_associations(
        IdentifiableObjectRepositoryInterface $productRepository,
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        AssociationInterface $xsellAssociation,
        ProductInterface $associated1,
        ProductInterface $associated2
    ) {
        $associationType->getCode()->willReturn('X_SELL');
        $xsellAssociation->getAssociationType()->willReturn($associationType);

        $associations = new ArrayCollection([
            $xsellAssociation->getWrappedObject(),
        ]);
        $product->getAssociations()->willReturn($associations);

        $productRepository->findOneByIdentifier('associated_1')->willReturn($associated1);
        $productRepository->findOneByIdentifier('associated_2')->willReturn($associated2);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $xsellAssociation->addProduct($associated1)->shouldBeCalled();
        $xsellAssociation->addProduct($associated2)->shouldBeCalled();

        $product->setAssociations($associations)->shouldBeCalled();

        $data = [
            'X_SELL' => [
                'products' => ['associated_1', 'associated_2'],
            ],
        ];

        $this->addFieldData($product, 'associations', $data);
    }

    function it_adds_product_model_associations(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        AssociationInterface $xsellAssociation,
        ProductModelInterface $model1,
        ProductModelInterface $model2
    ) {
        $associationType->getCode()->willReturn('X_SELL');
        $xsellAssociation->getAssociationType()->willReturn($associationType);

        $associations = new ArrayCollection([
            $xsellAssociation->getWrappedObject(),
        ]);
        $product->getAssociations()->willReturn($associations);

        $productModelRepository->findOneByIdentifier('model_1')->willReturn($model1);
        $productModelRepository->findOneByIdentifier('model_2')->willReturn($model2);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $xsellAssociation->addProductModel($model1)->shouldBeCalled();
        $xsellAssociation->addProductModel($model2)->shouldBeCalled();
        $product->setAssociations($associations)->shouldBeCalled()->willReturn($product);

        $data = [
            'X_SELL' => [
                'product_models' => ['model_1', 'model_2'],
            ],
        ];

        $this->addFieldData($product, 'associations', $data);
    }

    function it_adds_group_associations(
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        AssociationInterface $xsellAssociation,
        GroupInterface $blackFriday,
        GroupInterface $halloween
    ) {
        $associationType->getCode()->willReturn('X_SELL');
        $xsellAssociation->getAssociationType()->willReturn($associationType);

        $associations = new ArrayCollection([
            $xsellAssociation->getWrappedObject(),
        ]);
        $product->getAssociations()->willReturn($associations);


        $groupRepository->findOneByIdentifier('black_friday')->willReturn($blackFriday);
        $groupRepository->findOneByIdentifier('halloween')->willReturn($halloween);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $xsellAssociation->addGroup($blackFriday)->shouldBeCalled();
        $xsellAssociation->addGroup($halloween)->shouldBeCalled();

        $product->setAssociations($associations)->shouldBeCalled()->willReturn($product);

        $data = [
            'X_SELL' => [
                'groups' => ['black_friday', 'halloween'],
            ],
        ];

        $this->addFieldData($product, 'associations', $data);
    }

    function it_adds_several_associations(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $product,
        AssociationTypeInterface $xsellType,
        AssociationInterface $xsellAssociation,
        AssociationTypeInterface $upsellType,
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
        $xsellType->getCode()->willReturn('xsell');
        $xsellAssociation->getAssociationType()->willReturn($xsellType);
        $upsellType->getCode()->willReturn('upsell');
        $upsellAssociation->getAssociationType()->willReturn($upsellType);

        $associations = new ArrayCollection([
            $xsellAssociation->getWrappedObject(),
            $upsellAssociation->getWrappedObject(),
        ]);
        $product->getAssociations()->willReturn($associations);

        $productRepository->findOneByIdentifier('assocProductOne')->willReturn($assocProductOne);
        $productRepository->findOneByIdentifier('assocProductTwo')->willReturn($assocProductTwo);
        $productRepository->findOneByIdentifier('assocProductThree')->willReturn($assocProductThree);

        $productModelRepository->findOneByIdentifier('assocProductModelOne')->willReturn($assocProductModelOne);
        $productModelRepository->findOneByIdentifier('assocProductModelTwo')->willReturn($assocProductModelTwo);
        $productModelRepository->findOneByIdentifier('assocProductModelThree')->willReturn($assocProductModelThree);

        $groupRepository->findOneByIdentifier('assocGroupOne')->willReturn($assocGroupOne);
        $groupRepository->findOneByIdentifier('assocGroupTwo')->willReturn($assocGroupTwo);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $xsellAssociation->addProduct($assocProductOne)->shouldBeCalled();
        $xsellAssociation->addProduct($assocProductTwo)->shouldBeCalled();
        $xsellAssociation->addGroup($assocGroupOne)->shouldBeCalled();
        $xsellAssociation->addProductModel($assocProductModelOne)->shouldBeCalled();
        $xsellAssociation->addProductModel($assocProductModelTwo)->shouldBeCalled();

        $upsellAssociation->addProduct($assocProductThree)->shouldBeCalled();
        $upsellAssociation->addProductModel($assocProductModelThree)->shouldBeCalled();
        $upsellAssociation->addGroup($assocGroupTwo)->shouldBeCalled();

        $product->setAssociations($associations)->shouldBeCalled()->willReturn($product);

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
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        AssociationInterface $assoc666,
        ProductInterface $assocProductOne,
        ProductInterface $assocProductTwo,
        GroupInterface $assocGroupOne,
        GroupInterface $assocGroupTwo
    ) {
        $associationType->getCode()->willReturn('666');
        $assoc666->getAssociationType()->willReturn($associationType);
        $associations = new ArrayCollection([
            $assoc666->getWrappedObject(),
        ]);
        $product->getAssociations()->willReturn($associations);

        $productRepository->findOneByIdentifier('assocProductOne')->willReturn($assocProductOne);
        $productRepository->findOneByIdentifier('assocProductTwo')->willReturn($assocProductTwo);

        $groupRepository->findOneByIdentifier('assocGroupOne')->willReturn($assocGroupOne);
        $groupRepository->findOneByIdentifier('assocGroupTwo')->willReturn($assocGroupTwo);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $assoc666->addProduct($assocProductOne)->shouldBeCalled();
        $assoc666->addProduct($assocProductTwo)->shouldBeCalled();
        $assoc666->addGroup($assocGroupOne)->shouldBeCalled();

        $product->setAssociations($associations)->shouldBeCalled()->willReturn($product);

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
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $product
    ) {
        $product->getAssociations()->willReturn(new ArrayCollection());
        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();

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
        MissingAssociationAdder $missingAssociationAdder,
        IdentifiableObjectRepositoryInterface $productRepository,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        AssociationInterface $xsellAssociation
    ) {
        $associationType->getCode()->willReturn('xsell');
        $xsellAssociation->getAssociationType()->willReturn($associationType);
        $product->getAssociations()->willReturn(new ArrayCollection([$xsellAssociation->getWrappedObject()]));
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
        MissingAssociationAdder $missingAssociationAdder,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        AssociationInterface $xsellAssociation
    ) {
        $associationType->getCode()->willReturn('xsell');
        $xsellAssociation->getAssociationType()->willReturn($associationType);
        $product->getAssociations()->willReturn(new ArrayCollection([$xsellAssociation->getWrappedObject()]));
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
