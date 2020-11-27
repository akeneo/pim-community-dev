<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AssociationFieldAdder;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\AssociationFieldSetter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\FieldSetterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AssociationFieldSetterSpec extends ObjectBehavior
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

    function it_is_a_setter()
    {
        $this->shouldImplement(SetterInterface::class);
        $this->shouldImplement(FieldSetterInterface::class);
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
                AssociationFieldSetter::class,
                'not an array'
            )
        )->during('setFieldData', [$product, 'associations', 'not an array']);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "0".',
                AssociationFieldSetter::class,
                [0 => []]
            )
        )->during('setFieldData', [$product, 'associations', [0 => []]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                AssociationFieldSetter::class,
                ['assoc_type_code' => []]
            )
        )->during('setFieldData', [$product, 'associations', ['assoc_type_code' => []]]);

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                AssociationFieldSetter::class,
                ['assoc_type_code' => ['products' => [1], 'groups' => [], 'product_models' => [],]]
            )
        )->during(
            'setFieldData',
            [
                $product,
                'associations',
                ['assoc_type_code' => ['products' => [1], 'groups' => [], 'product_models' => []]],
            ]
        );

        $this->shouldThrow(
            InvalidPropertyTypeException::validArrayStructureExpected(
                'associations',
                'association format is not valid for the association type "assoc_type_code".',
                AssociationFieldSetter::class,
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
                AssociationFieldSetter::class,
                'Property "products" in association "assoc_type_code" expects an array as data, "string" given.',
                200
            )
        )->during(
            'setFieldData',
            [
                $product,
                'associations',
                ['assoc_type_code' => ['products' => 'string', 'groups' => [], 'product_models' => []]],
            ]
        );
    }

    function it_sets_association_field(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        MissingAssociationAdder $missingAssociationAdder,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $product,
        AssociationInterface $xsellAssociation,
        AssociationTypeInterface $xsellAssociationType
    ) {
        $xsellAssociationType->getCode()->willReturn('xsell');
        $xsellAssociationType->isTwoWay()->willReturn(false);
        $xsellAssociationType->isQuantified()->willReturn(false);
        $xsellAssociation->getAssociationType()->willReturn($xsellAssociationType);
        $associationTypeRepository->findOneByIdentifier('xsell')->willReturn($xsellAssociationType);

        $product->getAssociations()->willReturn(new ArrayCollection([$xsellAssociation->getWrappedObject()]));

        $assocProductOne = (new Product())->setIdentifier('assocProductOne');
        $assocProductTwo = (new Product())->setIdentifier('assocProductTwo');
        $assocProductThree = (new Product())->setIdentifier('assocProductThree');
        $assocProductModelOne = new ProductModel();
        $assocProductModelOne->setCode('assocProductModelOne');
        $assocProductModelTwo = new ProductModel();
        $assocProductModelTwo->setCode('assocProductModelTwo');
        $assocProductModelThree = new ProductModel();
        $assocProductModelThree->setCode('assocProductModelThree');
        $assocGroupOne = new Group();
        $assocGroupOne->setCode('assocGroupOne');
        $assocGroupTwo = new Group();
        $assocGroupTwo->setCode('assocGroupTwo');

        $productRepository->findOneByIdentifier('assocProductOne')->willReturn($assocProductOne);
        $productRepository->findOneByIdentifier('assocProductTwo')->willReturn($assocProductTwo);
        $productRepository->findOneByIdentifier('assocProductThree')->willReturn($assocProductThree);

        $productModelRepository->findOneByIdentifier('assocProductModelOne')->willReturn($assocProductModelOne);
        $productModelRepository->findOneByIdentifier('assocProductModelTwo')->willReturn($assocProductModelTwo);
        $productModelRepository->findOneByIdentifier('assocProductModelThree')->willReturn($assocProductModelThree);

        $groupRepository->findOneByIdentifier('assocGroupOne')->willReturn($assocGroupOne);
        $groupRepository->findOneByIdentifier('assocGroupTwo')->willReturn($assocGroupTwo);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $product->getAssociatedProducts('xsell')->shouldBeCalled()->willReturn(
            new ArrayCollection([$assocProductOne, $assocProductThree])
        );
        $product->getAssociatedProductModels('xsell')->shouldBeCalled()->willReturn(
            new ArrayCollection([$assocProductModelThree])
        );
        $product->getAssociatedGroups('xsell')->shouldBeCalled()->willReturn(
            new ArrayCollection([$assocGroupOne, $assocGroupTwo])
        );

        $product->removeAssociatedProduct($assocProductThree, 'xsell')->shouldBeCalled();
        $product->addAssociatedProduct($assocProductTwo, 'xsell')->shouldBeCalled();
        $product->removeAssociatedProductModel($assocProductModelThree, 'xsell')->shouldBeCalled();
        $product->addAssociatedProductModel($assocProductModelOne, 'xsell')->shouldBeCalled();
        $product->addAssociatedProductModel($assocProductModelTwo, 'xsell')->shouldBeCalled();
        $product->removeAssociatedGroup($assocGroupTwo, 'xsell')->shouldBeCalled();

        $product->removeAssociatedProduct($assocProductOne, 'xsell')->shouldNotBeCalled();
        $product->addAssociatedProduct($assocProductOne, 'xsell')->shouldNotBeCalled();
        $product->addAssociatedGroup(Argument::cetera())->shouldNotBeCalled();

        $this->setFieldData(
            $product,
            'associations',
            [
                'xsell' => [
                    'products' => ['assocProductOne', 'assocProductTwo'],
                    'product_models' => ['assocProductModelOne', 'assocProductModelTwo'],
                    'groups' => ['assocGroupOne'],
                ],
            ]
        );
    }

    function it_creates_inversed_association_on_product(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $productAssociated,
        ProductModelInterface $productModelAssociated
    ) {
        $compatibilityAssociationType = new AssociationType();
        $compatibilityAssociationType->setIsTwoWay(true);
        $compatibilityAssociationType->setCode('COMPATIBILITY');
        $associationTypeRepository->findOneByIdentifier('COMPATIBILITY')->willReturn($compatibilityAssociationType);

        $compatibilityAssociation = new ProductAssociation();
        $compatibilityAssociation->setAssociationType($compatibilityAssociationType);

        $product = new Product();
        $product->addAssociation($compatibilityAssociation);

        $productAssociated->getIdentifier()->willReturn('productAssociated');
        $productAssociated->hasAssociationForTypeCode('COMPATIBILITY')->willReturn(false);
        $productModelAssociated->getCode()->willReturn('productModelAssociated');
        $productModelAssociated->hasAssociationForTypeCode('COMPATIBILITY')->willReturn(true);
        $productRepository->findOneByIdentifier('productAssociated')->willReturn($productAssociated);
        $productModelRepository->findOneByIdentifier('productModelAssociated')->willReturn($productModelAssociated);

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();
        $missingAssociationAdder->addMissingAssociations($productAssociated)->shouldBeCalled();
        $productAssociated->addAssociatedProduct($product, 'COMPATIBILITY')->shouldBeCalled();
        $missingAssociationAdder->addMissingAssociations($productModelAssociated)->shouldNotBeCalled();
        $productModelAssociated->addAssociatedProduct($product, 'COMPATIBILITY')->shouldBeCalled();

        $this->setFieldData(
            $product,
            'associations',
            [
                'COMPATIBILITY' => [
                    'products' => ['productAssociated'],
                    'product_models' => ['productModelAssociated'],
                ],
            ]
        );
    }

    function it_removes_inversed_association_on_product(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        ProductInterface $productAssociated,
        ProductModelInterface $productModelAssociated
    ) {
        $compatibilityAssociationType = new AssociationType();
        $compatibilityAssociationType->setIsTwoWay(true);
        $compatibilityAssociationType->setCode('COMPATIBILITY');
        $associationTypeRepository->findOneByIdentifier('COMPATIBILITY')->willReturn($compatibilityAssociationType);

        $compatibilityAssociation = new ProductAssociation();
        $compatibilityAssociation->setAssociationType($compatibilityAssociationType);
        $compatibilityAssociation->addProduct($productAssociated->getWrappedObject());
        $compatibilityAssociation->addProductModel($productModelAssociated->getWrappedObject());

        $product = new Product();
        $product->addAssociation($compatibilityAssociation);

        $productAssociated->getIdentifier()->willReturn('productAssociated');
        $productModelAssociated->getCode()->willReturn('productModelAssociated');
        $productRepository->findOneByIdentifier('productAssociated')->willReturn($productAssociated);
        $productModelRepository->findOneByIdentifier('productModelAssociated')->willReturn($productModelAssociated);

        $productAssociated->removeAssociatedProduct($product, 'COMPATIBILITY')->shouldBeCalled();
        $productModelAssociated->removeAssociatedProduct($product, 'COMPATIBILITY')->shouldBeCalled();

        $this->setFieldData(
            $product,
            'associations',
            [
                'COMPATIBILITY' => [
                    'products' => [],
                    'product_models' => [],
                ],
            ]
        );
    }

    function it_creates_and_removes_inversed_association_on_product_model(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        AssociationTypeRepositoryInterface $associationTypeRepository,
        MissingAssociationAdder $missingAssociationAdder,
        ProductInterface $productAssociated,
        ProductModelInterface $productModelAssociated
    ) {
        $compatibilityAssociationType = new AssociationType();
        $compatibilityAssociationType->setIsTwoWay(true);
        $compatibilityAssociationType->setCode('COMPATIBILITY');
        $associationTypeRepository->findOneByIdentifier('COMPATIBILITY')->willReturn($compatibilityAssociationType);

        $compatibilityAssociation = new ProductModelAssociation();
        $compatibilityAssociation->setAssociationType($compatibilityAssociationType);
        $compatibilityAssociation->addProductModel($productModelAssociated->getWrappedObject());

        $productModel = new ProductModel();
        $productModel->addAssociation($compatibilityAssociation);

        $productAssociated->getIdentifier()->willReturn('productAssociated');
        $productAssociated->hasAssociationForTypeCode('COMPATIBILITY')->willReturn(false);
        $productModelAssociated->getCode()->willReturn('productModelAssociated');
        $productRepository->findOneByIdentifier('productAssociated')->willReturn($productAssociated);
        $productModelRepository->findOneByIdentifier('productModelAssociated')->willReturn($productModelAssociated);

        $missingAssociationAdder->addMissingAssociations($productModel)->shouldBeCalled();
        $missingAssociationAdder->addMissingAssociations($productAssociated)->shouldBeCalled();
        $productAssociated->addAssociatedProductModel($productModel, 'COMPATIBILITY')->shouldBeCalled();
        $productModelAssociated->removeAssociatedProductModel($productModel, 'COMPATIBILITY')->shouldBeCalled();

        $this->setFieldData(
            $productModel,
            'associations',
            [
                'COMPATIBILITY' => [
                    'products' => ['productAssociated'],
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
                AssociationFieldSetter::class,
                'non valid association type code'
            )
        )->during(
            'setFieldData',
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

        $product->getAssociatedProducts('xsell')->willReturn(new ArrayCollection());
        $product->getAssociatedProductModels('xsell')->willReturn(new ArrayCollection());
        $product->getAssociatedGroups('xsell')->willReturn(new ArrayCollection());

        $missingAssociationAdder->addMissingAssociations($product)->shouldBeCalled();

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product identifier',
                'The product does not exist',
                AssociationFieldSetter::class,
                'not existing product'
            )
        )->during(
            'setFieldData',
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
        ProductModelInterface $productModel,
        AssociationTypeInterface $associationType
    ) {
        $associationType->getCode()->willReturn('xsell');
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(false);
        $associationTypeRepository->findOneByIdentifier('xsell')->willReturn($associationType);
        $productModelRepository->findOneByIdentifier('not existing product model')->willReturn(null);

        $productModel->getAssociatedProducts('xsell')->willReturn(new ArrayCollection());
        $productModel->getAssociatedProductModels('xsell')->willReturn(new ArrayCollection());
        $productModel->getAssociatedGroups('xsell')->willReturn(new ArrayCollection());

        $missingAssociationAdder->addMissingAssociations($productModel)->shouldBeCalled();

        $this->shouldThrow(
            InvalidPropertyException::validEntityCodeExpected(
                'associations',
                'product model identifier',
                'The product model does not exist',
                AssociationFieldSetter::class,
                'not existing product model'
            )
        )->during(
            'setFieldData',
            [
                $productModel,
                'associations',
                ['xsell' => ['product_models' => ['not existing product model']]],
            ]
        );
    }
}
