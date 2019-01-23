<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobParameters;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\Collection;

class UpdatedFranklinIdentifiersSpec extends ObjectBehavior
{
    public function it_is_a_constraint_collection_provider(): void
    {
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    public function it_is_a_default_values_provider(): void
    {
        $this->shouldImplement(DefaultValuesProviderInterface::class);
    }

    public function it_only_supports_identify_products_to_resubscribe_job_instances(
        JobInterface $identifyJob,
        JobInterface $otherJob
    ): void {
        $identifyJob->getName()->willReturn(JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE);
        $otherJob->getName()->willReturn('another_job_name');

        $this->supports($identifyJob)->shouldReturn(true);
        $this->supports($otherJob)->shouldReturn(false);
    }

    public function it_provides_a_constraint_collection(): void
    {
        $this->getConstraintCollection()->shouldBeAnInstanceOf(Collection::class);
    }

    public function it_provides_a_default_value_for_updated_identifiers(): void
    {
        $defaultValues = $this->getDefaultValues();
        $defaultValues->shouldBeArray();
        $defaultValues->shouldHaveKey('updated_identifiers');
    }
}
