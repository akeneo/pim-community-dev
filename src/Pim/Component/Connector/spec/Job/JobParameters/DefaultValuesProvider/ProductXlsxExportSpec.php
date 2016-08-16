<?php

namespace spec\Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductXlsxExportSpec extends ObjectBehavior
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
                    '.' === $parameters['decimalSeparator'] &&
                    'yyyy-MM-dd' === $parameters['dateFormat'] &&
                    true === $parameters['with_media'] &&
                    10000 === $parameters['linesPerFile'] &&
                    is_array($parameters['filters']) &&
                    is_array($parameters['filters']['data']) &&
                    is_array($parameters['filters']['structure']);
            }
        ];
    }
}
