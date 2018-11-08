<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductModelCsvImport;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelCsvImportSpec extends ObjectBehavior
{
    function let(ConstraintCollectionProviderInterface $decoratedProvider)
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
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    function it_provides_constraints_collection(
        $decoratedProvider,
        Collection $decoratedCollection
    ) {
        $decoratedProvider->getConstraintCollection()->willReturn($decoratedCollection);
        $collection = $this->getConstraintCollection();
        $collection->shouldReturnAnInstanceOf(Collection::class);
        $fields = $collection->fields;
        $fields->shouldHaveCount(8);
        $fields->shouldHaveKey('decimalSeparator');
        $fields->shouldHaveKey('dateFormat');
        $fields->shouldHaveKey('enabled');
        $fields->shouldHaveKey('categoriesColumn');
        $fields->shouldHaveKey('enabledComparison');
        $fields->shouldHaveKey('realTimeVersioning');
        $fields->shouldHaveKey('invalid_items_file_format');
        $fields->shouldHaveKey('familyVariantColumn');
    }

    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }
}
