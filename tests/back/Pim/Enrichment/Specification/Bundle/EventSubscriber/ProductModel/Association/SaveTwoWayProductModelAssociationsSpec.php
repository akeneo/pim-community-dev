<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ProductModel\Association;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\TwoWayProductModelAssociationsSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class SaveTwoWayProductModelAssociationsSpec extends ObjectBehavior
{
    public function let(
        ProductModel $productModel,
        ProductModelAssociationInterface $oneWayAssociation,
        ProductModelAssociationInterface $twoWayAssociation,
        AssociationTypeInterface $oneWayAssociationType,
        AssociationTypeInterface $twoWayAssociationType,
        TwoWayProductModelAssociationsSaver $productModelAssociationsSaver
    ) {
        $productModel->getId()->willReturn(42);

        $twoWayAssociation->getAssociationType()->willReturn($twoWayAssociationType);
        $twoWayAssociationType->isTwoWay()->willReturn(true);

        $oneWayAssociation->getAssociationType()->willReturn($oneWayAssociationType);
        $oneWayAssociationType->isTwoWay()->willReturn(false);

        $this->beConstructedWith($productModelAssociationsSaver);
    }

    public function it_subscribe_to_the_save_event()
    {
        $this::getSubscribedEvents()->shouldReturn([
            'akeneo.storage.post_save' => 'saveInvertedAssociations',
        ]);
    }

    public function it_does_nothing_when_its_not_a_product_model(
        TwoWayProductModelAssociationsSaver $productModelAssociationsSaver,
        GenericEvent $event,
        Product $product
    ) {
        $event->getSubject()->willReturn($product);

        $productModelAssociationsSaver->saveInvertedAssociations(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->saveInvertedAssociations($event);
    }

    public function it_does_nothing_when_the_product_model_does_not_have_two_way_association(
        ProductModelAssociationInterface $oneWayAssociation,
        TwoWayProductModelAssociationsSaver $productModelAssociationsSaver,
        GenericEvent $event,
        Product $productModel,
        Collection $associationCollection
    ) {
        $associationCollection->toArray()->willReturn([$oneWayAssociation]);
        $productModel->getAssociations()->willReturn($associationCollection);
        $event->getSubject()->willReturn($productModel);

        $productModelAssociationsSaver->saveInvertedAssociations(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->saveInvertedAssociations($event);
    }

    public function it_attempts_to_save_inverted_association_when_product_model_have_two_way_association(
        TwoWayProductModelAssociationsSaver $productModelAssociationsSaver,
        GenericEvent $event,
        ProductModelInterface $productModel,
        ProductModelAssociationInterface $twoWayAssociation,
        Collection $associationCollection
    ) {
        $associationCollection->toArray()->willReturn([$twoWayAssociation]);
        $productModel->getAssociations()->willReturn($associationCollection);
        $event->getSubject()->willReturn($productModel);

        $productModelAssociationsSaver->saveInvertedAssociations($productModel, [$twoWayAssociation])->shouldBeCalled();

        $this->saveInvertedAssociations($event);
    }

    public function it_attempts_to_save_only_inverted_two_way_association(
        TwoWayProductModelAssociationsSaver $productModelAssociationsSaver,
        GenericEvent $event,
        ProductModelInterface $productModel,
        ProductModelAssociationInterface $twoWayAssociation,
        ProductModelAssociationInterface $oneWayAssociation,
        Collection $associationCollection
    ) {
        $associationCollection->toArray()->willReturn([$oneWayAssociation, $twoWayAssociation]);
        $productModel->getAssociations()->willReturn($associationCollection);
        $event->getSubject()->willReturn($productModel);

        $productModelAssociationsSaver->saveInvertedAssociations($productModel, [$twoWayAssociation])->shouldBeCalled();

        $this->saveInvertedAssociations($event);
    }
}
