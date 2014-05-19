<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DateTimeNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer_and_a_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_normalization_of_date_in_the_proposal_format(\DateTime $date)
    {
        $this->supportsNormalization($date, 'proposal')->shouldBe(true);
    }

    function it_normalizes_date_using_the_ISO8601_format(\DateTime $date)
    {
        $date->format(\DateTime::ISO8601)->willReturn('date');

        $this->normalize($date, 'proposal')->shouldReturn('date');
    }

    function it_supports_denormalization_of_date_attribute_in_the_proposal_format()
    {
        $this->supportsDenormalization([], 'pim_catalog_date', 'proposal')->shouldBe(true);
    }

    function it_denormalizes_the_date_in_the_datetime_context_instance()
    {
        $date = $this->denormalize('tomorrow', 'pim_catalog_date', 'proposal', ['instance' => new \DateTime('now')]);
        $date->format('Y-m-d')->shouldBe((new \DateTime('tomorrow'))->format('Y-m-d'));
    }
}
