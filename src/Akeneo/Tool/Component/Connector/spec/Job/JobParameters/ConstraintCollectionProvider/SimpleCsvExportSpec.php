<?php

namespace spec\Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use PhpSpec\ObjectBehavior;

class SimpleCsvExportSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(['my_supported_job_name']);
    }

    public function it_is_a_provider(): void
    {
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    public function it_provides_constraints_collection(): void
    {
        $collection = $this->getConstraintCollection();
        $collection->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\Collection');
        $fields = $collection->fields;
        $fields->shouldHaveCount(6);
        $fields->shouldHaveKey('storage');
        $fields->shouldHaveKey('delimiter');
        $fields->shouldHaveKey('enclosure');
        $fields->shouldHaveKey('withHeader');
        $fields->shouldHaveKey('users_to_notify');
        $fields->shouldHaveKey('is_user_authenticated');
    }

    public function it_supports_a_job(JobInterface $job): void
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }
}
