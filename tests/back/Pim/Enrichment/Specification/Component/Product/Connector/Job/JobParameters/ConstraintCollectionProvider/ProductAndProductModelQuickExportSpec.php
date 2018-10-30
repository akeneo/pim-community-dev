<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductAndProductModelQuickExport;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductAndProductModelQuickExportSpec extends ObjectBehavior
{
    function is_is_initializable()
    {
        $this->shouldHaveType(ProductAndProductModelQuickExport::class);
    }

    function let(
        ConstraintCollectionProviderInterface $simpleConstraint
    ) {
        $this->beConstructedWith(
            $simpleConstraint, ['csv_product_quick_export']
        );
    }

    function it_supports_job_interface(
        JobInterface $job
    ) {
        $job->getName()->willReturn('csv_product_quick_export');

        $this->supports($job)->shouldReturn(true);
    }

    function it_does_not_support_job_interface(
        JobInterface $job
    ) {
        $job->getName()->willReturn('foobar');

        $this->supports($job)->shouldReturn(false);
    }

    function it_should_return_a_constraint_collection(
        $simpleConstraint
    ) {
        $simpleConstraint->getConstraintCollection()->willReturn(new Collection([
            'fields' => [
                'filePath' => [new NotBlank(['groups' => ['Execution']])]
            ]
        ]));

        $this->getConstraintCollection()->shouldHaveType(Collection::class);
    }
}
