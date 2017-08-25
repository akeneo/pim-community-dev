<?php

namespace spec\Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use PhpSpec\ObjectBehavior;

class SimpleCsvExportSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['my_supported_job_name']);
    }

    function it_is_a_provider()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface');
    }

    function it_provides_constraints_collection()
    {
        $collection = $this->getConstraintCollection();
        $collection->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\Collection');
        $fields = $collection->fields;
        $fields->shouldHaveCount(5);
        $fields->shouldHaveKey('filePath');
        $fields->shouldHaveKey('delimiter');
        $fields->shouldHaveKey('enclosure');
        $fields->shouldHaveKey('withHeader');
        $fields->shouldHaveKey('user_to_notify');
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }
}
