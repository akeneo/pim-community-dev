<?php

namespace spec\Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductCsvExportSpec extends ObjectBehavior
{
    function let(DefaultValuesProviderInterface $decoratedProvider)
    {
        $this->beConstructedWith($decoratedProvider, ['my_supported_job_name']);
    }

    function it_is_a_provider()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface');
    }

    function it_provides_default_values($decoratedProvider)
    {
        $decoratedProvider->getDefaultValues()->willReturn(['decoratedParam' => true]);
        $this->getDefaultValues()->shouldReturn([
            'decoratedParam'         => true,
            'decimalSeparator'       => '.',
            'dateFormat'             => 'yyyy-MM-dd',
            'channel'                => null,
            'locales'                => [],
            'enabled'                => 'enabled',
            'updated_since_strategy' => 'all',
            'updated_since_date'     => null,
            'families'               => null,
            'completeness'           => 'at_least_one_complete',
        ]);
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }
}
