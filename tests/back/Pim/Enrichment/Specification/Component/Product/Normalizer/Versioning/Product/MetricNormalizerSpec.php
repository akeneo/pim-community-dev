<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetricNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf(NormalizerInterface::class);
    }

    function it_supports_flat_normalization_of_product_metric(MetricInterface $metric)
    {
        $this->supportsNormalization($metric, 'flat')->shouldBe(true);
    }

    function it_does_not_support_flat_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'flat')->shouldBe(false);
    }

    function it_normalizes_metric_in_many_fields_by_default(MetricInterface $metric)
    {
        $metric->getData()->willReturn('72.1000');
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight'])
            ->shouldReturn(['weight' => '72.1000', 'weight-unit' => 'KILOGRAM']);
    }

    function it_normalizes_metric_in_many_fields(MetricInterface $metric)
    {
        $metric->getData()->willReturn('72.1000');
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight', 'metric_format' => 'multiple_fields'])
            ->shouldReturn(['weight' => '72.1000', 'weight-unit' => 'KILOGRAM']);
    }

    function it_normalizes_null_metric_in_many_fields(MetricInterface $metric)
    {
        $metric->getData()->willReturn(null);
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight', 'metric_format' => 'multiple_fields'])
            ->shouldReturn(['weight' => '', 'weight-unit' => '']);
    }

    function it_normalizes_empty_metric_in_many_fields(MetricInterface $metric)
    {
        $metric->getData()->willReturn('');
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight', 'metric_format' => 'multiple_fields'])
            ->shouldReturn(['weight' => '', 'weight-unit' => '']);
    }

    function it_normalizes_metric_in_one_field(MetricInterface $metric)
    {
        $metric->getData()->willReturn('72.1000');
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight', 'metric_format' => 'single_field'])
            ->shouldReturn(['weight' => '72.1000 KILOGRAM']);
    }

    function it_normalizes_null_metric_in_one_fields(MetricInterface $metric)
    {
        $metric->getData()->willReturn(null);
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight', 'metric_format' => 'single_field'])
            ->shouldReturn(['weight' => '']);
    }

    function it_normalizes_empty_metric_in_one_fields(MetricInterface $metric)
    {
        $metric->getData()->willReturn('');
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight', 'metric_format' => 'single_field'])
            ->shouldReturn(['weight' => '']);
    }

    function it_normalizes_metric_with_float_data_with_decimals_allowed_by_default(MetricInterface $metric)
    {
        $metric->getData()->willReturn('72.1000');
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight'])
            ->shouldReturn(['weight' => '72.1000', 'weight-unit' => 'KILOGRAM']);
    }

    function it_normalizes_metric_with_float_data_with_decimals_allowed(MetricInterface $metric)
    {
        $metric->getData()->willReturn('72.1000');
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight', 'decimals_allowed' => true])
            ->shouldReturn(['weight' => '72.1000', 'weight-unit' => 'KILOGRAM']);
    }

    function it_normalizes_metric_with_float_data_with_decimals_not_allowed(MetricInterface $metric)
    {
        $metric->getData()->willReturn('72.0000');
        $metric->getUnit()->willReturn('KILOGRAM');

        $this
            ->normalize($metric, null, ['field_name' => 'weight', 'decimals_allowed' => false])
            ->shouldReturn(['weight' => '72', 'weight-unit' => 'KILOGRAM']);
    }

    function it_throws_exception_when_the_context_field_name_key_is_not_provided(MetricInterface $metric)
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    'Missing required "field_name" context value, got "metric_format, foo, bar"'
                )
            )
            ->duringNormalize($metric, null, ['foo' => true, 'bar' => true]);
    }

    function it_throws_exception_when_the_context_metric_format_is_not_valid(MetricInterface $metric)
    {
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    'Value "foo" of "metric_format" context value is not allowed ' .
                    '(allowed values: "single_field, multiple_fields"'
                )
            )
            ->duringNormalize($metric, null, ['field_name' => 'weight', 'metric_format' => 'foo']);
    }
}
