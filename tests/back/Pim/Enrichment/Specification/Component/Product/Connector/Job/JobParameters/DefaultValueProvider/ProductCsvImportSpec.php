<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;

class ProductCsvImportSpec extends ObjectBehavior
{
    function let(DefaultValuesProviderInterface $decoratedProvider)
    {
        $this->beConstructedWith($decoratedProvider, ['my_supported_job_name']);
    }

    function it_is_a_provider()
    {
        $this->shouldImplement(DefaultValuesProviderInterface::class);
    }

    function it_provides_default_values($decoratedProvider)
    {
        $decoratedProvider->getDefaultValues()->willReturn(['decoratedParam' => true]);
        $this->getDefaultValues()->shouldReturn(
            [
                'decoratedParam'     => true,
                'decimalSeparator'   => ".",
                'dateFormat'         => "yyyy-MM-dd",
                'enabled'            => true,
                'categoriesColumn'   => "categories",
                'familyColumn'       => "family",
                'groupsColumn'       => "groups",
                'enabledComparison'  => true,
                'realTimeVersioning' => true,
            ]
        );
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }
}
