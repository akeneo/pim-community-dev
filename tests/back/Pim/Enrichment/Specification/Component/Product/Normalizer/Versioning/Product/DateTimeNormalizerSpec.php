<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf(NormalizerInterface::class);
    }

    function it_supports_csv_normalization_of_date()
    {
        $this->supportsNormalization(new \DateTime(), 'csv')->shouldBe(true);
    }

    function it_supports_flat_normalization_of_date()
    {
        $this->supportsNormalization(new \DateTime(), 'flat')->shouldBe(true);
    }

    function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_date_value_using_the_default_format()
    {
        $date = new \DateTime();

        $this
            ->normalize($date, null, ['field_name' => 'release_date'])
            ->shouldReturn(['release_date' => $date->format('c')]);
    }

    function it_normalizes_date_value_using_the_context_format()
    {
        $date = new \DateTime();

        $this
            ->normalize($date, null, ['field_name' => 'release_date', 'format' => 'd/m/Y'])
            ->shouldReturn(['release_date' => $date->format('d/m/Y')]);
    }

    function it_normalizes_date_value_using_the_default_format_overriden_in_ctor()
    {
        $this->beConstructedWith(\DateTime::RFC822);
        $date = new \DateTime();

        $this
            ->normalize($date, null, ['field_name' => 'release_date'])
            ->shouldReturn(['release_date' => $date->format(\DateTime::RFC822)]);
    }

    function it_throws_exception_when_the_context_field_name_key_is_not_provided()
    {
        $this
            ->shouldThrow(new \InvalidArgumentException('Missing required "field_name" context value, got "foo, bar"'))
            ->duringNormalize(false, null, ['foo' => true, 'bar' => true]);
    }
}
