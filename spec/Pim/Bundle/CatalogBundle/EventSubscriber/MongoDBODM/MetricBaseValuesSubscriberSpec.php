<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber\MongoDBODM;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 * @require Doctrine\ODM\MongoDB\UnitOfWork
 */
class MetricBaseValuesSubscriberSpec extends ObjectBehavior
{
    function let(MeasureConverter $converter, MeasureManager $manager)
    {
        $this->beConstructedWith($converter, $manager);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_prePersist_and_preUpdate()
    {
        $this->getSubscribedEvents()->shouldReturn(['prePersist', 'preUpdate']);
    }

    function it_converts_metric_data_before_persisting(
        LifecycleEventArgs $args,
        MetricInterface $metric,
        MeasureManager $manager,
        MeasureConverter $converter,
        ProductInterface $product,
        ProductValueInterface $productValue,
        DocumentManager $dm
    ) {
        $args->getObject()->willReturn($product);
        $product->getValues()->willReturn([$productValue]);
        $productValue->getData()->willReturn($metric);
        $metric->getId()->willReturn(null);

        $metric->getUnit()->willReturn('cm');
        $metric->getFamily()->willReturn('distance');
        $metric->getData()->willReturn(100);

        $manager->getStandardUnitForFamily('distance')->willReturn('m');
        $converter->setFamily('distance')->shouldBeCalled()->willReturn($converter);
        $converter->convertBaseToStandard('cm', 100)->willReturn(1);

        $metric->setBaseData(1)->shouldBeCalled()->willReturn($metric);
        $metric->setBaseUnit('m')->shouldBeCalled();

        $metric->getValue()->willReturn($productValue);
        $productValue->getEntity()->willReturn($product);
        $args->getObjectManager()->willReturn($dm);

        $this->prePersist($args);
    }

    function it_converts_metric_data_before_updating(
        LifecycleEventArgs $args,
        MetricInterface $metric,
        MeasureManager $manager,
        MeasureConverter $converter,
        DocumentManager $dm,
        ProductInterface $product,
        ProductValueInterface $productValue,
        UnitOfWork $uow,
        ClassMetadata $metadata
    ) {
        $args->getObject()->willReturn($product);
        $product->getValues()->willReturn([$productValue]);
        $productValue->getData()->willReturn($metric);
        $metric->getId()->willReturn(12);

        $args->getObjectManager()->willReturn($dm);
        $dm->getUnitOfWork()->willReturn($uow);
        $uow->recomputeSingleDocumentChangeSet(
            Argument::type('Doctrine\Common\Persistence\Mapping\ClassMetadata'),
            $metric
        )->shouldBeCalled();

        $metric->getUnit()->willReturn('cm');
        $metric->getFamily()->willReturn('distance');
        $metric->getData()->willReturn(100);

        $manager->getStandardUnitForFamily('distance')->willReturn('m');
        $converter->setFamily('distance')->shouldBeCalled()->willReturn($converter);
        $converter->convertBaseToStandard('cm', 100)->willReturn(1);

        $metric->setBaseData(1)->shouldBeCalled()->willReturn($metric);
        $metric->setBaseUnit('m')->shouldBeCalled();

        $dm->getClassMetadata(Argument::any())->willReturn($metadata);
        $dm->getUnitOfWork()->willReturn($uow);
        $uow->recomputeSingleDocumentChangeSet(
            $metadata,
            $metric
        )->shouldBeCalled();

        $this->preUpdate($args);
    }
}
