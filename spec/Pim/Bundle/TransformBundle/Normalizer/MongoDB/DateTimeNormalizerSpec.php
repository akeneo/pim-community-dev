<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use Akeneo\Bundle\StorageUtilsBundle\MongoDB\MongoObjectsFactory;
use PhpSpec\ObjectBehavior;

/**
 * @require \MongoId
 */
class DateTimeNormalizerSpec extends ObjectBehavior
{
    function let(MongoObjectsFactory $mongoFactory)
    {
        $this->beConstructedWith($mongoFactory);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_datetime_in_mongodb_document(\DateTime $dateTime)
    {
        $this->supportsNormalization($dateTime, 'mongodb_document')->shouldReturn(true);
    }

    function it_does_not_support_normalization_of_other_entities(\StdClass $object)
    {
        $this->supportsNormalization($object, 'mongodb_document')->shouldReturn(false);
    }

    function it_does_not_support_normalization_into_other_format(\DateTime $dateTime)
    {
        $this->supportsNormalization($dateTime, 'json')->shouldReturn(false);
    }

    function it_normalizes_a_datetime(
        $mongoFactory,
        \DateTime $dateTime,
        \MongoDate $mongoDate
    ) {
        $dateTime->getTimestamp()->willReturn(266225724);
        $mongoFactory->createMongoDate(266225724)->willReturn($mongoDate);

        $this->normalize($dateTime, 'mongodb_document')->shouldReturn($mongoDate);
    }
}
