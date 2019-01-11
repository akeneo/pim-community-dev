<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobParameters;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobParameters\DateTimeJobParameterToFetchSubscriptions;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class DateTimeJobParameterToFetchSubscriptionsSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DateTimeJobParameterToFetchSubscriptions::class);
    }

    public function it_is_a_constraint_collection_provider(): void
    {
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    public function it_is_a_default_values_provider(): void
    {
        $this->shouldImplement(DefaultValuesProviderInterface::class);
    }

    public function it_only_supports_fetch_products_job(JobInterface $job): void
    {
        $job->getName()->willReturn(JobInstanceNames::FETCH_PRODUCTS);
        $this->supports($job)->shouldReturn(true);

        $job->getName()->willReturn(Argument::not(JobInstanceNames::FETCH_PRODUCTS));
        $this->supports($job)->shouldReturn(false);
    }

    public function it_returns_a_default_value_for_updated_since_field(): void
    {
        $this->getDefaultValues()->shouldReturn(['updated_since' => null]);
    }

    public function it_returns_a_constraint_collection(): void
    {
        $this->getConstraintCollection()->shouldBeAnInstanceOf(Collection::class);
    }
}
