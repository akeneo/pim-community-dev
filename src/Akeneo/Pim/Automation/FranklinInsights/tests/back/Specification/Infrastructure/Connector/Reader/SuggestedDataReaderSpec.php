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
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SuggestedDataReaderSpec extends ObjectBehavior
{
    public function let(
        SubscriptionProviderInterface $subscriptionProvider
    ): void {
        $this->beConstructedWith($subscriptionProvider);
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
}
