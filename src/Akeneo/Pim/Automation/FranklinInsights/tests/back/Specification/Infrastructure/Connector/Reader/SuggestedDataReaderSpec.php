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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Reader;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectLastCompletedFetchExecutionDatetimeQuery;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SuggestedDataReaderSpec extends ObjectBehavior
{
    public function let(
        SubscriptionProviderInterface $subscriptionProvider,
        SelectLastCompletedFetchExecutionDatetimeQuery $query,
        StepExecution $stepExecution
    ): void {
        $this->beConstructedWith($subscriptionProvider, $query);
        $this->setStepExecution($stepExecution);
    }

    public function it_is_a_reader(): void
    {
        $this->shouldImplement(ItemReaderInterface::class);
    }

    public function it_is_initializable(): void
    {
        $this->shouldImplement(InitializableInterface::class);
    }

    public function it_is_step_execution_aware(): void
    {
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    public function it_initializes_the_reader_with_the_updated_since_parameter($stepExecution, $subscriptionProvider): void
    {
        $datetime = new \DateTime('2013-01-16');
        $jobParameters = new JobParameters(['updated_since' => $datetime]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);

        $subscriptionProvider->fetch($datetime)->shouldBeCalled();

        $this->initialize();
    }

    public function it_takes_the_last_execution_datetime_if_no_parameter($stepExecution, $subscriptionProvider, $query): void
    {
        $jobParameters = new JobParameters(['updated_since' => null]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $query->execute()->willReturn('2017-08-28 11:26:32');

        $subscriptionProvider->fetch(new \Datetime('2017-08-28 11:26:32'))->shouldBeCalled();

        $this->initialize();
    }

    public function it_generates_an_old_date_for_the_first_execution($stepExecution, $subscriptionProvider, $query): void
    {
        $jobParameters = new JobParameters(['updated_since' => null]);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $query->execute()->willReturn('2012-01-01');

        $subscriptionProvider->fetch(new \Datetime('2012-01-01'))->shouldBeCalled();

        $this->initialize();
    }
}
