<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductModelCsvImport;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelCsvImportSpec extends ObjectBehavior
{
    function let(DefaultValuesProviderInterface $decoratedProvider)
    {
        $this->beConstructedWith($decoratedProvider, ['my_supported_job_name']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(
            ProductModelCsvImport::class);
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
                'decoratedParam' => true,
                'decimalSeparator' => ".",
                'dateFormat' => "yyyy-MM-dd",
                'enabled' => true,
                'categoriesColumn' => "categories",
                'familyVariantColumn' => 'family_variant',
                'enabledComparison' => true,
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
