<?php

namespace spec\Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Collection;

class ProductCsvExportSpec extends ObjectBehavior
{
    function let(ConstraintCollectionProviderInterface $decoratedProvider)
    {
        $this->beConstructedWith($decoratedProvider, ['my_supported_job_name']);
    }

    function it_is_a_provider()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface');
    }

    function it_provides_constraints_collection(
        $decoratedProvider,
        Collection $decoratedCollection
    ) {
        $decoratedProvider->getConstraintCollection()->willReturn($decoratedCollection);
        $collection = $this->getConstraintCollection();
        $collection->shouldReturnAnInstanceOf('Symfony\Component\Validator\Constraints\Collection');
        $fields = $collection->fields;
        $fields->shouldHaveKey('decimalSeparator');
        $fields->shouldHaveKey('dateFormat');
        $fields->shouldHaveKey('channel');
        $fields->shouldHaveKey('enabled');
        $fields->shouldHaveKey('locales');
        $fields->shouldHaveKey('families');
        $fields->shouldHaveKey('completeness');
        $fields->shouldHaveKey('updated_since_strategy');
        $fields->shouldHaveKey('updated_since_date');
        $fields->shouldHaveKey('updated_since_n_days');
        $fields->shouldHaveKey('product_identifier');
        $fields->shouldHaveKey('categories');
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }
}
