<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AssociationFieldAdder;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\FieldAdderInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class AssociationFieldAdderSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder,
        AssociationTypeRepositoryInterface $associationTypeRepository
    ) {
        $this->beConstructedWith(
            $productRepository,
            $productModelRepository,
            $groupRepository,
            $missingAssociationAdder,
            $associationTypeRepository,
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
            [
                $product,
                'associations',
                ['assoc_type_code' => ['products' => [], 'groups' => [2], 'product_models' => [],]],
            ]
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
            [
                $product,
                'associations',
                ['assoc_type_code' => ['products' => 'string', 'groups' => [], 'product_models' => []]],
            ]
        );
    }

    function it_adds_product_associations(
        IdentifiableObjectRepositoryInterface $productRepository,
        MissingAssociationAdder $missingAssociationAdder,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        ProductInterface $associated1,
        ProductInterface $associated2
    ) {
        $associationType->getCode()->willReturn('X_SELL');
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('X_SELL')->willReturn($associationType);

        $productRepository->findOneByIdentifier('associated_1')->willReturn($associated1);
        $productRepository->findOneByIdentifier('associated_2')->willReturn($associated2);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->addAssociatedProduct($associated1, 'X_SELL')->shouldBeCalled();
        $product->addAssociatedProduct($associated2, 'X_SELL')->shouldBeCalled();

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
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        ProductModelInterface $model1,
        ProductModelInterface $model2
    ) {
        $associationType->getCode()->willReturn('X_SELL');
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('X_SELL')->willReturn($associationType);

        $productModelRepository->findOneByIdentifier('model_1')->willReturn($model1);
        $productModelRepository->findOneByIdentifier('model_2')->willReturn($model2);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->addAssociatedProductModel($model1, 'X_SELL')->shouldBeCalled();
        $product->addAssociatedProductModel($model2, 'X_SELL')->shouldBeCalled();

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
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        GroupInterface $blackFriday,
        GroupInterface $halloween
    ) {
        $associationType->getCode()->willReturn('X_SELL');
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('X_SELL')->willReturn($associationType);

        $groupRepository->findOneByIdentifier('black_friday')->willReturn($blackFriday);
        $groupRepository->findOneByIdentifier('halloween')->willReturn($halloween);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->addAssociatedGroup($blackFriday, 'X_SELL')->shouldBeCalled();
        $product->addAssociatedGroup($halloween, 'X_SELL')->shouldBeCalled();

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
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationTypeInterface $xsellType,
        AssociationTypeInterface $upsellType,
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
        $xsellType->isTwoWay()->willReturn(false);
        $xsellType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('xsell')->willReturn($xsellType);

        $upsellType->getCode()->willReturn('upsell');
        $upsellType->isTwoWay()->willReturn(false);
        $upsellType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('upsell')->willReturn($upsellType);

        $productRepository->findOneByIdentifier('assocProductOne')->willReturn($assocProductOne);
        $productRepository->findOneByIdentifier('assocProductTwo')->willReturn($assocProductTwo);
        $productRepository->findOneByIdentifier('assocProductThree')->willReturn($assocProductThree);

        $productModelRepository->findOneByIdentifier('assocProductModelOne')->willReturn($assocProductModelOne);
        $productModelRepository->findOneByIdentifier('assocProductModelTwo')->willReturn($assocProductModelTwo);
        $productModelRepository->findOneByIdentifier('assocProductModelThree')->willReturn($assocProductModelThree);

        $groupRepository->findOneByIdentifier('assocGroupOne')->willReturn($assocGroupOne);
        $groupRepository->findOneByIdentifier('assocGroupTwo')->willReturn($assocGroupTwo);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();

        $product->addAssociatedProduct($assocProductOne, 'xsell')->shouldBeCalled();
        $product->addAssociatedProduct($assocProductTwo, 'xsell')->shouldBeCalled();
        $product->addAssociatedProductModel($assocProductModelOne, 'xsell')->shouldBeCalled();
        $product->addAssociatedProductModel($assocProductModelTwo, 'xsell')->shouldBeCalled();
        $product->addAssociatedGroup($assocGroupOne, 'xsell')->shouldBeCalled();

        $product->addAssociatedProduct($assocProductThree, 'upsell')->shouldBeCalled();
        $product->addAssociatedProductModel($assocProductModelThree, 'upsell')->shouldBeCalled();
        $product->addAssociatedGroup($assocGroupTwo, 'upsell')->shouldBeCalled();

        $this->addFieldData(
            $product,
            'associations',
            [
                'xsell' => [
                    'products' => ['assocProductOne', 'assocProductTwo'],
                    'product_models' => ['assocProductModelOne', 'assocProductModelTwo'],
                    'groups' => ['assocGroupOne'],
                ],
                'upsell' => [
                    'products' => ['assocProductThree'],
                    'product_models' => ['assocProductModelThree'],
                    'groups' => ['assocGroupTwo'],
                ],
            ]
        );
    }

    function it_adds_association_field_even_when_the_association_type_code_is_a_string_representing_an_integer(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationTypeInterface $associationType,
        ProductInterface $assocProductOne,
        GroupInterface $assocGroupOne
    ) {
        $associationType->getCode()->willReturn('666');
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('666')->willReturn($associationType);

        $productRepository->findOneByIdentifier('assocProductOne')->willReturn($assocProductOne);
        $groupRepository->findOneByIdentifier('assocGroupOne')->willReturn($assocGroupOne);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->addAssociatedProduct($assocProductOne, '666')->shouldBeCalled();
        $product->addAssociatedGroup($assocGroupOne, '666')->shouldBeCalled();

        $this->addFieldData(
            $product,
            'associations',
            [
                '666' => [
                    'products' => ['assocProductOne'],
                    'groups' => ['assocGroupOne'],
                    'product_models' => [],
                ],
            ]
        );
    }

    function it_fails_if_one_of_the_association_type_code_does_not_exist(
        MissingAssociationAdder $missingAssociationAdder,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product
    ) {
        $associationTypeRepository->findOneByIdentifier('non valid association type code')->willReturn(null);
        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'association type code',
                'The association type does not exist or is quantified',
                AssociationFieldAdder::class,
                'non valid association type code'
            )
        )->during(
            'addFieldData',
            [
                $product,
                'associations',
                ['non valid association type code' => ['groups' => [], 'products' => [], 'product_models' => []]],
            ]
        );
    }

    function it_fails_if_one_of_the_associated_product_does_not_exist(
        MissingAssociationAdder $missingAssociationAdder,
        IdentifiableObjectRepositoryInterface $productRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationTypeInterface $associationType
    ) {
        $associationType->getCode()->willReturn('xsell');
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('xsell')->willReturn($associationType);
        $productRepository->findOneByIdentifier('not existing product')->willReturn(null);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();

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
                ['xsell' => ['products' => ['not existing product']]],
            ]
        );
    }

    function it_fails_if_one_of_the_associated_product_models_does_not_exist(
        MissingAssociationAdder $missingAssociationAdder,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationTypeInterface $associationType
    ) {
        $associationType->getCode()->willReturn('xsell');
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('xsell')->willReturn($associationType);
        $productModelRepository->findOneByIdentifier('not existing product model')->willReturn(null);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product model identifier',
                'The product model does not exist',
                AssociationFieldAdder::class,
                'not existing product model'
            )
        )->during(
            'addFieldData',
            [
                $product,
                'associations',
                ['xsell' => ['product_models' => ['not existing product model']]],
            ]
        );
    }

    function it_fails_if_one_of_the_associated_group_does_not_exist(
        MissingAssociationAdder $missingAssociationAdder,
        IdentifiableObjectRepositoryInterface $groupRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationTypeInterface $associationType
    ) {
        $associationType->getCode()->willReturn('xsell');
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('xsell')->willReturn($associationType);
        $groupRepository->findOneByIdentifier('not existing group')->willReturn(null);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();

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
                ['xsell' => ['groups' => ['not existing group']]],
            ]
        );
    }

    function it_adds_inversed_associations_for_a_product(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        MissingAssociationAdder $missingAssociationAdder,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationTypeInterface $twoWayType,
        ProductInterface $assocProduct,
        ProductModelInterface $assocProductModel
    ) {
        $twoWayType->getCode()->willReturn('TWOWAY');
        $twoWayType->isTwoWay()->willReturn(true);
        $twoWayType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('TWOWAY')->willReturn($twoWayType);

        $productRepository->findOneByIdentifier('assocProduct')->willReturn($assocProduct);
        $productModelRepository->findOneByIdentifier('assocProductModel')->willReturn($assocProductModel);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->addAssociatedProduct($assocProduct, 'TWOWAY')->shouldBeCalled();
        $product->addAssociatedProductModel($assocProductModel, 'TWOWAY')->shouldBeCalled();

        $assocProduct->hasAssociationForTypeCode('TWOWAY')->willReturn(false);
        $missingAssociationAdder->addMissingAssociations($assocProduct)->shouldBeCalled();
        $assocProduct->addAssociatedProduct($product, 'TWOWAY')->shouldBeCalled();

        $assocProductModel->hasAssociationForTypeCode('TWOWAY')->willReturn(true);
        $missingAssociationAdder->addMissingAssociations($assocProductModel)->shouldNotBeCalled();
        $assocProductModel->addAssociatedProduct($product, 'TWOWAY')->shouldBeCalled();

        $this->addFieldData(
            $product,
            'associations',
            [
                'TWOWAY' => [
                    'products' => ['assocProduct'],
                    'product_models' => ['assocProductModel'],
                ]
            ]
        );
    }

    function it_adds_inversed_associations_for_a_product_model(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        MissingAssociationAdder $missingAssociationAdder,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductModelInterface $productModel,
        AssociationTypeInterface $twoWayType,
        ProductInterface $assocProduct,
        ProductModelInterface $assocProductModel
    ) {
        $twoWayType->getCode()->willReturn('TWOWAY');
        $twoWayType->isTwoWay()->willReturn(true);
        $twoWayType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('TWOWAY')->willReturn($twoWayType);

        $productRepository->findOneByIdentifier('assocProduct')->willReturn($assocProduct);
        $productModelRepository->findOneByIdentifier('assocProductModel')->willReturn($assocProductModel);

        $missingAssociationAdder->addMissingAssociations($productModel)->shouldBeCalled();
        $productModel->addAssociatedProduct($assocProduct, 'TWOWAY')->shouldBeCalled();
        $productModel->addAssociatedProductModel($assocProductModel, 'TWOWAY')->shouldBeCalled();

        $assocProduct->hasAssociationForTypeCode('TWOWAY')->willReturn(false);
        $missingAssociationAdder->addMissingAssociations($assocProduct)->shouldBeCalled();
        $assocProduct->addAssociatedProductModel($productModel, 'TWOWAY')->shouldBeCalled();

        $assocProductModel->hasAssociationForTypeCode('TWOWAY')->willReturn(true);
        $missingAssociationAdder->addMissingAssociations($assocProductModel)->shouldNotBeCalled();
        $assocProductModel->addAssociatedProductModel($productModel, 'TWOWAY')->shouldBeCalled();

        $this->addFieldData(
            $productModel,
            'associations',
            [
                'TWOWAY' => [
                    'products' => ['assocProduct'],
                    'product_models' => ['assocProductModel'],
                ]
            ]
        );
    }
}
