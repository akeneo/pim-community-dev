<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\PhpMemoryLimit;
use PhpSpec\ObjectBehavior;


class PhpMemoryLimitSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PhpMemoryLimit::class);
    }

    function it_returns_the_correct_integer_value_when_the_input_is_numeric()
    {
        $this->asBytes('123')->shouldReturn(123);
    }

    function it_truncates_comma_to_integer()
    {
        $this->asBytes('123,456,789')->shouldReturn(123);
    }

    function it_truncates_float_to_integer()
    {
        $this->asBytes('12.34')->shouldReturn(12);
    }

    function it_trims_leading_and_trailing_whitespace()
    {
        $this->asBytes(' 123 ')->shouldReturn(123);
    }

    function it_returns_maximum_memory_when_input_is_empty()
    {
        $this->asBytes('')->shouldReturn(PhpMemoryLimit::MAXIMUM_MEMORY_IN_BYTES);
    }

    function it_returns_maximum_memory_when_input_is_minus_one()
    {
        $this->asBytes('-1')->shouldReturn(PhpMemoryLimit::MAXIMUM_MEMORY_IN_BYTES);
    }

    function it_returns_default_memory_when_input_is_negative()
    {
        $this->asBytes('-100')->shouldReturn(PhpMemoryLimit::DEFAULT_MEMORY_IN_BYTES_WHEN_NOT_SET);
    }

    function it_returns_default_memory_when_input_is_zero()
    {
        $this->asBytes('0')->shouldReturn(PhpMemoryLimit::DEFAULT_MEMORY_IN_BYTES_WHEN_NOT_SET);
    }

    function it_returns_memory_when_set_with_kilobytes()
    {
        $this->asBytes('10K')->shouldReturn(10240);
    }

    function it_returns_memory_when_set_with_megabytes()
    {
        $this->asBytes('10M')->shouldReturn(10485760);
    }

    function it_returns_memory_when_set_with_gigabytes()
    {
        $this->asBytes('10G')->shouldReturn(10737418240);
    }
}
