<?php

namespace spec\Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use PhpSpec\ObjectBehavior;

class SimpleCsvImportSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(['my_supported_job_name']);
    }

    public function it_is_a_provider(): void
    {
        $this->shouldImplement(DefaultValuesProviderInterface::class);
    }

    public function it_provides_default_values(): void
    {
        $this->getDefaultValues()->shouldReturn(
            [
                'storage' => [
                    'type' => 'none',
                ],
                'delimiter' => ";",
                'enclosure' => '"',
                'escape' => '\\',
                'withHeader' => true,
                'uploadAllowed' => true,
                'invalid_items_file_format' => 'csv',
                'users_to_notify' => [],
                'is_user_authenticated' => false,
            ]
        );
    }

    public function it_supports_a_job(JobInterface $job): void
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }
}
