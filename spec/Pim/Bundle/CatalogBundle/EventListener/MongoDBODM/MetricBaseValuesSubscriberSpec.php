<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Pim\Bundle\CatalogBundle\Model\Metric;

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
        Metric $metric,
        MeasureManager $manager,
        MeasureConverter $converter
    ) {
        $args->getObject()->willReturn($metric);

        $metric->getUnit()->willReturn('cm');
        $metric->getFamily()->willReturn('distance');
        $metric->getData()->willReturn(100);

        $manager->getStandardUnitForFamily('distance')->willReturn('m');
        $converter->setFamily('distance')->shouldBeCalled()->willReturn($converter);
        $converter->convertBaseToStandard('cm', 100)->willReturn(1);

        $metric->setBaseData(1)->shouldBeCalled()->willReturn($metric);
        $metric->setBaseUnit('m')->shouldBeCalled();

        $this->prePersist($args);
    }

    function it_converts_metric_data_before_updating(
        LifecycleEventArgs $args,
        Metric $metric,
        MeasureManager $manager,
        MeasureConverter $converter,
        DocumentManager $dm,
        UnitOfWork $uow
    ) {
        $args->getObject()->willReturn($metric);
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

        $this->preUpdate($args);
    }
}
