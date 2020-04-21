<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\Association;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\TwoWayProductAssociationsSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class SaveTwoWayProductAssociationsSpec extends ObjectBehavior
{
    public function let(
        Product $product,
        ProductAssociationInterface $oneWayAssociation,
        ProductAssociationInterface $twoWayAssociation,
        AssociationTypeInterface $oneWayAssociationType,
        AssociationTypeInterface $twoWayAssociationType,
        TwoWayProductAssociationsSaver $productAssociationsSaver
    ) {
        $product->getId()->willReturn(42);

        $twoWayAssociation->getAssociationType()->willReturn($twoWayAssociationType);
        $twoWayAssociationType->isTwoWay()->willReturn(true);

        $oneWayAssociation->getAssociationType()->willReturn($oneWayAssociationType);
        $oneWayAssociationType->isTwoWay()->willReturn(false);

        $this->beConstructedWith($productAssociationsSaver);
    }

    public function it_subscribe_to_the_save_event()
    {
        $this::getSubscribedEvents()->shouldReturn([
            'akeneo.storage.post_save' => 'saveInvertedAssociations',
        ]);
    }

    public function it_does_nothing_when_its_not_a_product(
        TwoWayProductAssociationsSaver $productAssociationsSaver,
        GenericEvent $event,
        ProductModel $productModel
    ) {
        $event->getSubject()->willReturn($productModel);

        $productAssociationsSaver->saveInvertedAssociations(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->saveInvertedAssociations($event);
    }

    public function it_does_nothing_when_the_product_does_not_have_two_way_association(
        ProductAssociationInterface $oneWayAssociation,
        TwoWayProductAssociationsSaver $productAssociationsSaver,
        GenericEvent $event,
        Product $product,
        Collection $associationCollection
    ) {
        $associationCollection->toArray()->willReturn([$oneWayAssociation]);
        $product->getAssociations()->willReturn($associationCollection);
        $event->getSubject()->willReturn($product);

        $productAssociationsSaver->saveInvertedAssociations(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->saveInvertedAssociations($event);
    }

    public function it_attempts_to_save_inverted_association_when_product_have_two_way_association(
        TwoWayProductAssociationsSaver $productAssociationsSaver,
        GenericEvent $event,
        ProductInterface $product,
        ProductAssociationInterface $twoWayAssociation,
        Collection $associationCollection
    ) {
        $associationCollection->toArray()->willReturn([$twoWayAssociation]);
        $product->getAssociations()->willReturn($associationCollection);
        $event->getSubject()->willReturn($product);

        $productAssociationsSaver->saveInvertedAssociations($product, [$twoWayAssociation])->shouldBeCalled();

        $this->saveInvertedAssociations($event);
    }

    public function it_attempts_to_save_only_inverted_two_way_association(
        TwoWayProductAssociationsSaver $productAssociationsSaver,
        GenericEvent $event,
        ProductInterface $product,
        ProductAssociationInterface $twoWayAssociation,
        ProductAssociationInterface $oneWayAssociation,
        Collection $associationCollection
    ) {
        $associationCollection->toArray()->willReturn([$oneWayAssociation, $twoWayAssociation]);
        $product->getAssociations()->willReturn($associationCollection);
        $event->getSubject()->willReturn($product);

        $productAssociationsSaver->saveInvertedAssociations($product, [$twoWayAssociation])->shouldBeCalled();

        $this->saveInvertedAssociations($event);
    }
}
