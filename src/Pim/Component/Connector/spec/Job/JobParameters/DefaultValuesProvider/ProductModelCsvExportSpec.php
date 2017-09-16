<?php

namespace spec\Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;

class ProductModelCsvExportSpec extends ObjectBehavior
{
    function let(
        DefaultValuesProviderInterface $decoratedProvider
    ) {
        $this->beConstructedWith($decoratedProvider, ['my_supported_job_name']);
    }

    function it_is_a_provider()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface');
    }

    function it_provides_default_values(
        $decoratedProvider
    ) {
        $decoratedProvider->getDefaultValues()->willReturn(['decoratedParam' => true]);
        $this->getDefaultValues()->shouldReturnWellFormedDefaultValues();
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }

    public function getMatchers()
    {
        return [
            'returnWellFormedDefaultValues' => function ($parameters) {
                return true === $parameters['decoratedParam'] &&
                    true === $parameters['with_media'];
            }
        ];
    }
}
