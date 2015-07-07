<?php

namespace spec\Pim\Bundle\VersioningBundle\UpdateGuesser;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface;

class ProductValueUpdateGuesserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\UpdateGuesser\ProductValueUpdateGuesser');
    }

    function it_is_an_update_guesser()
    {
        $this->shouldImplement('Pim\Bundle\VersioningBundle\UpdateGuesser\UpdateGuesserInterface');
    }

    function it_supports_entity_updates_and_deletion()
    {
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_DELETE)->shouldReturn(true);
        $this->supportAction(UpdateGuesserInterface::ACTION_UPDATE_COLLECTION)->shouldReturn(false);
        $this->supportAction('foo')->shouldReturn(false);
    }

    function it_marks_product_as_updated_when_a_product_price_is_removed(
        EntityManager $em,
        UnitOfWork $unitOfWork,
        ProductInterface $product,
        ProductValueInterface $value,
        ProductPriceInterface $price
    ) {
        $price->getValue()->willReturn($value);
        $value->getEntity()->willReturn($product);
        $em->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getEntityState($product)->willReturn(UnitOfWork::STATE_MANAGED);
        $this->guessUpdates($em, $price, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$product]);
    }

    function it_marks_product_as_updated_when_a_product_media_is_removed(
        EntityManager $em,
        UnitOfWork $unitOfWork,
        ProductInterface $product,
        ProductValueInterface $value,
        ProductMediaInterface $media
    ) {
        $media->getValue()->willReturn($value);
        $value->getEntity()->willReturn($product);
        $em->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getEntityState($product)->willReturn(UnitOfWork::STATE_MANAGED);
        $this->guessUpdates($em, $media, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$product]);
    }

    function it_marks_product_as_updated_when_a_product_metric_is_removed(
        EntityManager $em,
        UnitOfWork $unitOfWork,
        ProductInterface $product,
        ProductValueInterface $value,
        MetricInterface $metric
    ) {
        $metric->getValue()->willReturn($value);
        $value->getEntity()->willReturn($product);
        $em->getUnitOfWork()->willReturn($unitOfWork);
        $unitOfWork->getEntityState($product)->willReturn(UnitOfWork::STATE_MANAGED);
        $this->guessUpdates($em, $metric, UpdateGuesserInterface::ACTION_DELETE)->shouldReturn([$product]);
    }

    function it_marks_product_as_updated_when_a_product_value_is_updated(
        EntityManager $em,
        ProductInterface $product,
        ProductValueInterface $value
    ) {
        $value->getEntity()->willReturn($product);
        $this->guessUpdates($em, $value, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$product]);
    }

    function it_marks_product_as_updated_when_a_product_price_is_updated(
        EntityManager $em,
        UnitOfWork $uow,
        ProductInterface $product,
        ProductValueInterface $value,
        ProductPriceInterface $price
    ) {
        $price->getValue()->willReturn($value);
        $value->getEntity()->willReturn($product);

        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityChangeSet($price)->willReturn(['data' => ['10', '11']]);

        $this->guessUpdates($em, $price, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$product]);
    }

    function it_marks_product_as_updated_when_a_product_media_is_updated(
        EntityManager $em,
        UnitOfWork $uow,
        ProductInterface $product,
        ProductValueInterface $value,
        ProductMediaInterface $media
    ) {
        $media->getValue()->willReturn($value);
        $value->getEntity()->willReturn($product);

        $this->guessUpdates($em, $media, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$product]);
    }

    function it_marks_product_as_updated_when_a_product_metric_is_updated(
        EntityManager $em,
        UnitOfWork $uow,
        ProductInterface $product,
        ProductValueInterface $value,
        MetricInterface $metric
    ) {
        $metric->getValue()->willReturn($value);
        $value->getEntity()->willReturn($product);

        $em->getUnitOfWork()->willReturn($uow);
        $uow->getEntityChangeSet($metric)->willReturn(['data' => ['20', '25']]);

        $this->guessUpdates($em, $metric, UpdateGuesserInterface::ACTION_UPDATE_ENTITY)->shouldReturn([$product]);
    }
}
