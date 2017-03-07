<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer\Document;

use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\Metric;

/**
 * @require \MongoId
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class MetricNormalizerSpec extends ObjectBehavior
{
    function let(MongoObjectsFactory $mongoFactory, MeasureConverter $converter, MeasureManager $manager)
    {
        $this->beConstructedWith($mongoFactory, $converter, $manager);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_metric_in_mongodb_document(Metric $metric)
    {
        $this->supportsNormalization($metric, 'mongodb_document')->shouldReturn(true);
    }

    function it_does_not_support_normalization_of_other_entities(\StdClass $object)
    {
        $this->supportsNormalization($object, 'mongodb_document')->shouldReturn(false);
    }

    function it_does_not_support_normalization_of_metric_into_other_format(Metric $metric)
    {
        $this->supportsNormalization($metric, 'json')->shouldReturn(false);
    }

    function it_normalizes_a_metric_into_mongodb_document(
        $mongoFactory,
        $converter,
        $manager,
        Metric $metric,
        \MongoId $mongoId
    ) {
        $mongoFactory->createMongoId()->willReturn($mongoId);

        $metric->getUnit()->willReturn('Kg');
        $metric->getData()->willReturn(85);
        $metric->getFamily()->willReturn('weight');

        $converter->setFamily('weight')->willReturn($converter);
        $converter->convertBaseToStandard('Kg', 85)->willReturn(8500);

        $manager->getStandardUnitForFamily('weight')->willReturn('g');

        $metric->setBaseData(8500)->shouldBeCalled();
        $metric->getBaseData()->willReturn(8500);
        $metric->setBaseUnit('g')->shouldBeCalled();
        $metric->getBaseUnit()->willReturn('g');

        $this->normalize($metric, 'mongodb_document')->shouldReturn([
            '_id'      => $mongoId,
            'family'   => 'weight',
            'unit'     => 'Kg',
            'data'     => 85,
            'baseUnit' => 'g',
            'baseData' => 8500
        ]);
    }
}
