<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\MongoDB;

use PhpSpec\ObjectBehavior;

/**
 * @require \MongoId
 */
class GenericNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_object_in_mongodb_document(\StdClass $object)
    {
        $this->supportsNormalization($object, 'mongodb_document')->shouldReturn(true);
    }

    function it_does_not_support_normalization_into_other_format(\StdClass $object)
    {
        $this->supportsNormalization($object, 'json')->shouldReturn(false);
    }
}
