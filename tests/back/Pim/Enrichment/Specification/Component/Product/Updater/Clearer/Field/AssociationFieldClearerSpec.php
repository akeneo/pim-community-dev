<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationFieldClearerSpec extends ObjectBehavior
{
    function let(TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater)
    {
        $this->beConstructedWith($twoWayAssociationUpdater);
    }

    function it_is_a_clearer()
    {
        $this->shouldImplement(ClearerInterface::class);
    }

    function it_supports_only_associations_field()
    {
        $this->supportsProperty('categories')->shouldReturn(false);
        $this->supportsProperty('other')->shouldReturn(false);
        $this->supportsProperty('associations')->shouldReturn(true);
    }

    function it_clears_all_associations(
        AssociationTypeInterface $xsellType,
        AssociationTypeInterface $upsellType,
        AssociationInterface $xsellAssociation,
        AssociationInterface $upsellAssociation,
        EntityWithAssociationsInterface $entity
    ) {
        $associatedProduct = new Product();
        $associatedProductModel = new ProductModel();
        $associatedGroup = new Group();

        $xsellType->getCode()->willReturn('XSELL');
        $xsellType->isTwoWay()->willReturn(false);
        $xsellAssociation->getAssociationType()->willReturn($xsellType);

        $upsellType->getCode()->willReturn('UPSELL');
        $upsellType->isTwoWay()->willReturn(false);
        $upsellAssociation->getAssociationType()->willReturn($upsellType);

        $entity->getAssociations()->willReturn(
            new ArrayCollection([$xsellAssociation->getWrappedObject(), $upsellAssociation->getWrappedObject()])
        );
        $entity->getAssociatedProducts('XSELL')->shouldBeCalled()->willReturn(
            new ArrayCollection([$associatedProduct])
        );
        $entity->getAssociatedProductModels('XSELL')->shouldBeCalled()->willReturn(
            new ArrayCollection([$associatedProductModel])
        );
        $entity->getAssociatedGroups('XSELL')->shouldBeCalled()->willReturn(new ArrayCollection([$associatedGroup]));
        $entity->getAssociatedProducts('UPSELL')->shouldBeCalled()->willReturn(new ArrayCollection());
        $entity->getAssociatedProductModels('UPSELL')->shouldBeCalled()->willReturn(
            new ArrayCollection([$associatedProductModel])
        );
        $entity->getAssociatedGroups('UPSELL')->shouldBeCalled()->willReturn(new ArrayCollection());

        $entity->removeAssociatedProduct($associatedProduct, 'XSELL')->shouldBeCalled();
        $entity->removeAssociatedProductModel($associatedProductModel, 'XSELL')->shouldBeCalled();
        $entity->removeAssociatedGroup($associatedGroup, 'XSELL')->shouldBeCalled();
        $entity->removeAssociatedProductModel($associatedProductModel, 'UPSELL')->shouldBeCalled();

        $this->clear($entity, 'associations');
    }

    function it_removes_inversed_associations_of_a_product(
        TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater,
        AssociationTypeInterface $xsellType,
        ProductInterface $associatedProduct,
        ProductModelInterface $associatedProductModel
    ) {
        $xsellType->getCode()->willReturn('XSELL');
        $xsellType->isTwoWay()->willReturn(true);

        $association = new ProductAssociation();
        $association->setAssociationType($xsellType->getWrappedObject());
        $association->addProduct($associatedProduct->getWrappedObject());
        $association->addProductModel($associatedProductModel->getWrappedObject());
        $product = new Product();
        $product->addAssociation($association);

        $twoWayAssociationUpdater->removeInversedAssociation(
            $product,
            'XSELL',
            $associatedProduct
        )->shouldBeCalled();
        $twoWayAssociationUpdater->removeInversedAssociation(
            $product,
            'XSELL',
            $associatedProductModel
        )->shouldBeCalled();

        $this->clear($product, 'associations');
    }

    function it_removes_inversed_associations_of_a_product_model(
        TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater,
        AssociationTypeInterface $xsellType,
        ProductInterface $associatedProduct,
        ProductModelInterface $associatedProductModel
    ) {
        $xsellType->getCode()->willReturn('XSELL');
        $xsellType->isTwoWay()->willReturn(true);

        $association = new ProductModelAssociation();
        $association->setAssociationType($xsellType->getWrappedObject());
        $association->addProduct($associatedProduct->getWrappedObject());
        $association->addProductModel($associatedProductModel->getWrappedObject());
        $productModel = new ProductModel();
        $productModel->addAssociation($association);

        $twoWayAssociationUpdater->removeInversedAssociation(
            $productModel,
            'XSELL',
            $associatedProduct
        )->shouldBeCalled();
        $twoWayAssociationUpdater->removeInversedAssociation(
            $productModel,
            'XSELL',
            $associatedProductModel
        )->shouldBeCalled();

        $this->clear($productModel, 'associations');
    }
}
