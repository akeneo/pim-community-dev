<?php

namespace spec\Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use PhpSpec\ObjectBehavior;

class SimpleCsvImportSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['my_supported_job_name']);
    }

    function it_is_a_provider()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface');
    }

    function it_provides_default_values()
    {
        $this->getDefaultValues()->shouldReturn(
            [
                'filePath'                  => null,
                'delimiter'                 => ";",
                'enclosure'                 => '"',
                'escape'                    => '\\',
                'withHeader'                => true,
                'uploadAllowed'             => true,
                'invalid_items_file_format' => 'csv',
                'notification_user'         => null,
            ]
        );
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }
}
