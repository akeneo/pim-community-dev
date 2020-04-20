<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class SaveTwoWayProductAssociationsSpec extends ObjectBehavior
{
    public function let(
        Connection $connectionNotUsed,
        Connection $connection,
        Product $product,
        ProductInterface $productWithTwoWayAssociation,
        ProductAssociationInterface $productAssociation,
        AssociationTypeInterface $associationType,
        Collection $associationCollection
    ) {
        $connectionNotUsed->beginTransaction()->shouldNotBeCalled();
        $connectionNotUsed->executeQuery(Argument::any())->shouldNotBeCalled();
        $connectionNotUsed->executeUpdate(Argument::any())->shouldNotBeCalled();

        $product->getId()->willReturn(42);
        $productWithTwoWayAssociation->getId()->willReturn(42);
        $productWithTwoWayAssociation->getAssociations()->willReturn($associationCollection);
        $associationCollection->toArray()->willReturn([$productAssociation]);
        $productAssociation->getAssociationType()->willReturn($associationType);
        $associationType->isTwoWay()->willReturn(true);
        $associationType->getId()->willReturn(1);

        $this->beConstructedWith($connection);
    }

    public function it_subscribe_to_the_save_event()
    {
        $this::getSubscribedEvents()->shouldReturn([
            'akeneo.storage.post_save' => 'saveInvertedAssociations',
        ]);
    }

    public function it_does_nothing_when_its_not_a_product(
        Connection $connectionNotUsed,
        GenericEvent $event,
        ProductModel $productModel
    ) {
        $this->beConstructedWith($connectionNotUsed);

        $event->getSubject()->willReturn($productModel);

        $this->saveInvertedAssociations($event);
    }

    public function it_does_nothing_when_the_product_does_not_have_two_way_association(
        Connection $connectionNotUsed,
        GenericEvent $event,
        Product $product,
        ProductAssociation $productAssociation,
        Collection $associationCollection,
        AssociationType $associationType
    ) {
        $this->beConstructedWith($connectionNotUsed);

        $event->getSubject()->willReturn($product);
        $associationCollection->toArray()->willReturn([$productAssociation]);
        $product->getAssociations()->willReturn($associationCollection);
        $productAssociation->getAssociationType()->willReturn($associationType);
        $associationType->getId()->willReturn(1);
        $associationType->isTwoWay()->willReturn(false);

        $this->saveInvertedAssociations($event);
    }

    public function it_attempts_to_save_inverted_association_when_product_have_two_way_association(
        Connection $connection,
        GenericEvent $event,
        ProductInterface $productWithTwoWayAssociation
    ) {
        $event->getSubject()->willReturn($productWithTwoWayAssociation);

        $connection->beginTransaction()->shouldBeCalled();
        $connection->executeUpdate(
            Argument::any(),
            ["association_type_ids" => [1], "owner_id" => 42],
            ["association_type_ids" => Connection::PARAM_INT_ARRAY]
        )->shouldBeCalled();

        $connection->commit()->shouldBeCalled();

        $this->saveInvertedAssociations($event);
    }

    public function it_rollback_and_throw_an_error_when_one_error_occurred(
        Connection $connection,
        GenericEvent $event,
        ProductInterface $productWithTwoWayAssociation
    ) {
        $event->getSubject()->willReturn($productWithTwoWayAssociation);

        $connection->beginTransaction()->shouldBeCalled();
        $connection->executeUpdate(Argument::any(), Argument::any(), Argument::any())->willThrow('\Exception');
        $connection->rollBack()->shouldBeCalled();

        $this->shouldThrow('\Exception')->duringSaveInvertedAssociations($event);
    }
}
