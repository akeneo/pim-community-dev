<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Updater;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Updater\TwoWayAssociationUpdater;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TwoWayAssociationUpdaterSpec extends ObjectBehavior
{
    function let(
        MissingAssociationAdder $missingAssociationAdder,
        ManagerRegistry $registry,
        EntityManager $entityManager
    ) {
        $registry->getManager()->willReturn($entityManager);

        $this->beConstructedWith($registry, $missingAssociationAdder);
    }

    public function it_is_a_two_way_association_updater(): void
    {
        $this->shouldHaveType(TwoWayAssociationUpdater::class);
        $this->shouldImplement(TwoWayAssociationUpdaterInterface::class);
    }

    public function it_adds_missing_association_and_associates_the_product(
        $missingAssociationAdder,
        ProductInterface $associatedProduct
    ): void {
        $owner = new Product();

        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(false);
        $missingAssociationAdder->addMissingAssociations($associatedProduct)->shouldBeCalled();
        $associatedProduct->addAssociatedProduct($owner, 'xsell')->shouldBeCalled();

        $this->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function it_associates_a_product(
        $missingAssociationAdder,
        ProductInterface $associatedProduct
    ): void {
        $owner = new Product();

        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(true);
        $associatedProduct->addAssociatedProduct($owner, 'xsell')->shouldBeCalled();

        $this->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function it_adds_missing_association_and_associates_the_product_model(
        $missingAssociationAdder,
        ProductInterface $associatedProduct
    ): void {
        $owner = new ProductModel();

        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(false);
        $missingAssociationAdder->addMissingAssociations($associatedProduct)->shouldBeCalled();
        $associatedProduct->addAssociatedProductModel($owner, 'xsell')->shouldBeCalled();

        $this->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function it_associates_a_product_model(
        $missingAssociationAdder,
        ProductInterface $associatedProduct
    ): void {
        $owner = new ProductModel();

        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(true);
        $associatedProduct->addAssociatedProductModel($owner, 'xsell')->shouldBeCalled();

        $this->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function it_associates_only_product_or_product_model(
        $missingAssociationAdder,
        ProductInterface $associatedProduct,
        EntityWithAssociationsInterface $owner
    ): void {
        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(true);
        $associatedProduct->addAssociatedProduct(Argument::cetera())->shouldNotBeCalled();
        $associatedProduct->addAssociatedProductModel(Argument::cetera())->shouldNotBeCalled();

        $this
            ->shouldThrow('\LogicException')
            ->during('createInversedAssociation', [$owner, 'xsell', $associatedProduct]);
    }

    public function it_adds_missing_association_and_removes_the_product(
        $missingAssociationAdder,
        $entityManager,
        ProductInterface $associatedProduct
    ): void {
        $owner = new Product();

        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(false);
        $missingAssociationAdder->addMissingAssociations($associatedProduct)->shouldBeCalled();
        $associatedProduct->removeAssociatedProduct($owner, 'xsell')->shouldBeCalled();
        $entityManager->persist($associatedProduct)->shouldBeCalled();

        $this->removeInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function it_removes_a_product(
        $missingAssociationAdder,
        $entityManager,
        ProductInterface $associatedProduct
    ): void {
        $owner = new Product();

        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(true);
        $associatedProduct->removeAssociatedProduct($owner, 'xsell')->shouldBeCalled();
        $entityManager->persist($associatedProduct)->shouldBeCalled();

        $this->removeInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function it_adds_missing_association_and_removes_the_product_model(
        $missingAssociationAdder,
        $entityManager,
        ProductInterface $associatedProduct
    ): void {
        $owner = new ProductModel();

        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(false);
        $missingAssociationAdder->addMissingAssociations($associatedProduct)->shouldBeCalled();
        $associatedProduct->removeAssociatedProductModel($owner, 'xsell')->shouldBeCalled();
        $entityManager->persist($associatedProduct)->shouldBeCalled();

        $this->removeInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function it_removes_a_product_model(
        $missingAssociationAdder,
        $entityManager,
        ProductInterface $associatedProduct
    ): void {
        $owner = new ProductModel();

        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(true);
        $associatedProduct->removeAssociatedProductModel($owner, 'xsell')->shouldBeCalled();
        $entityManager->persist($associatedProduct)->shouldBeCalled();

        $this->removeInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function it_removes_only_product_or_product_model(
        $missingAssociationAdder,
        $entityManager,
        ProductInterface $associatedProduct,
        EntityWithAssociationsInterface $owner
    ): void {
        $associatedProduct->hasAssociationForTypeCode('xsell')->willReturn(true);
        $associatedProduct->removeAssociatedProduct(Argument::cetera())->shouldNotBeCalled();
        $associatedProduct->removeAssociatedProductModel(Argument::cetera())->shouldNotBeCalled();
        $entityManager->persist($associatedProduct)->shouldNotBeCalled();

        $this
            ->shouldThrow('\LogicException')
            ->during('removeInversedAssociation', [$owner, 'xsell', $associatedProduct]);
    }
}
