<?php

namespace spec\Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use PhpSpec\ObjectBehavior;

class SimpleYamlExportSpec extends ObjectBehavior
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
                    'file_path' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_%job_label%_%datetime%.yml',
                ],
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
