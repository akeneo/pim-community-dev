<?php

namespace spec\Pim\Component\Catalog\Updater\Adder;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AssociationInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

class AssociationFieldAdderSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductBuilderInterface $productBuilder
    ) {
        $this->beConstructedWith($productRepository, $groupRepository, $productBuilder, ['associations']);
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
            InvalidArgumentException::arrayExpected(
                'associations',
                'adder',
                'association',
                'string'
            )
        )->during('addFieldData', [$product, 'associations', 'not an array']);

        $this->shouldThrow(
            InvalidArgumentException::associationFormatExpected(
                'associations',
                [0 => []]
            )
        )->during('addFieldData', [$product, 'associations', [0 => []]]);

        $this->shouldThrow(
            InvalidArgumentException::associationFormatExpected(
                'associations',
                ['assoc_type_code' => []]
            )
        )->during('addFieldData', [$product, 'associations', ['assoc_type_code' => []]]);

        $this->shouldThrow(
            InvalidArgumentException::associationFormatExpected(
                'associations',
                ['assoc_type_code' => ['products' => []]]
            )
        )->during('addFieldData', [$product, 'associations', ['assoc_type_code' => ['products' => []]]]);

        $this->shouldThrow(
            InvalidArgumentException::associationFormatExpected(
                'associations',
                ['assoc_type_code' => ['groups' => []]]
            )
        )->during('addFieldData', [$product, 'associations', ['assoc_type_code' => ['groups' => []]]]);

        $this->shouldThrow(
            InvalidArgumentException::associationFormatExpected(
                'associations',
                ['assoc_type_code' => ['products' => [1], 'groups' => []]]
            )
        )->during(
            'addFieldData',
            [$product, 'associations', ['assoc_type_code' => ['products' => [1], 'groups' => []]]]
        );

        $this->shouldThrow(
            InvalidArgumentException::associationFormatExpected(
                'associations',
                ['assoc_type_code' => ['products' => [], 'groups' => [2]]]
            )
        )->during(
            'addFieldData',
            [$product, 'associations', ['assoc_type_code' => ['products' => [], 'groups' => [2]]]]
        );
    }

    function it_adds_association_field(
        $productRepository,
        $groupRepository,
        $productBuilder,
        ProductInterface $product,
        AssociationInterface $xsellAssociation,
        AssociationInterface $upsellAssociation,
        ProductInterface $assocProductOne,
        ProductInterface $assocProductTwo,
        ProductInterface $assocProductThree,
        GroupInterface $assocGroupOne,
        GroupInterface $assocGroupTwo
    ) {
        $productBuilder->addMissingAssociations($product)->shouldBeCalled();

        $product->getAssociationForTypeCode('xsell')->willReturn($xsellAssociation);
        $product->getAssociationForTypeCode('upsell')->willReturn($upsellAssociation);

        $productRepository->findOneByIdentifier('assocProductOne')->willReturn($assocProductOne);
        $productRepository->findOneByIdentifier('assocProductTwo')->willReturn($assocProductTwo);
        $productRepository->findOneByIdentifier('assocProductThree')->willReturn($assocProductThree);

        $groupRepository->findOneByIdentifier('assocGroupOne')->willReturn($assocGroupOne);
        $groupRepository->findOneByIdentifier('assocGroupTwo')->willReturn($assocGroupTwo);

        $xsellAssociation->addProduct($assocProductOne)->shouldBeCalled();
        $xsellAssociation->addProduct($assocProductTwo)->shouldBeCalled();
        $xsellAssociation->addGroup($assocGroupOne)->shouldBeCalled();

        $upsellAssociation->addProduct($assocProductThree)->shouldBeCalled();
        $upsellAssociation->addGroup($assocGroupTwo)->shouldBeCalled();

        $this->addFieldData(
            $product,
            'associations',
            [
                'xsell' => [
                    'products' => ['assocProductOne', 'assocProductTwo'],
                    'groups' => ['assocGroupOne']
                ],
                'upsell' => [
                    'products' => ['assocProductThree'],
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
            InvalidArgumentException::expected(
                'associations',
                'existing association type code',
                'adder',
                'association',
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
            InvalidArgumentException::expected(
                'associations',
                'existing product identifier',
                'adder',
                'association',
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
            InvalidArgumentException::expected(
                'associations',
                'existing group code',
                'adder',
                'association',
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
