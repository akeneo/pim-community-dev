<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\GenericEvent;

class SaveTwoWayProductAssociationsSpec extends ObjectBehavior
{
    public function let(
        Connection $connection
    ) {
        $this->beConstructedWith($connection);
    }

    public function it_subscribe_to_the_save_event()
    {
        $this::getSubscribedEvents()->shouldReturn([
            'akeneo.storage.post_save' => 'saveInvertedAssociations',
        ]);
    }

    public function it_does_nothing_when_its_not_a_product(
        Connection $connection,
        GenericEvent $event,
        ProductModel $productModel
    ) {
        $event->getSubject()->willReturn($productModel);

        $connection->executeQuery()->shouldNotBeCalled();
        $this->saveInvertedAssociations($event);
    }

    public function it_does_nothing_when_the_product_does_not_have_a_two_way_association(
        Connection $connection,
        GenericEvent $event,
        Product $product,
        ProductAssociation $productAssociation,
        AssociationType $associationType
    ) {
        $event->getSubject()->willReturn($product);
        $product->getAssociations()->willReturn([$productAssociation]);
        $productAssociation->getAssociationType()->willReturn($associationType);
        $associationType->isTwoWay()->willReturn(false);

        $connection->executeQuery()->shouldNotBeCalled();
        $this->saveInvertedAssociations($event);
    }
}
