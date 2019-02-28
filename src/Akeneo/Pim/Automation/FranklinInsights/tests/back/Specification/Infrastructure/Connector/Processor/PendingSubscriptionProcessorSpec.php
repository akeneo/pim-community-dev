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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Factory\ProposalSuggestedDataFactory;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Proposal\ValueObject\ProposalSuggestedData;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Processor\PendingSubscriptionProcessor;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class PendingSubscriptionProcessorSpec extends ObjectBehavior
{
    public function let(ProposalSuggestedDataFactory $suggestedDataFactory): void
    {
        $this->beConstructedWith($suggestedDataFactory);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PendingSubscriptionProcessor::class);
    }

    public function it_is_an_item_processor(): void
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    public function it_processes_a_subscription_in_proposal_suggested_data($suggestedDataFactory): void
    {
        $subscription = new ProductSubscription(42, new SubscriptionId('subscription-42'), ['asin' => 'asin-42']);
        $suggestedData = new ProposalSuggestedData(42, ['asin' => 'asin-42']);

        $suggestedDataFactory->fromSubscription($subscription)->willReturn($suggestedData);

        $this->process($subscription)->shouldReturn($suggestedData);
    }

    public function it_throws_an_invalid_item_exception_when_the_suggested_data_is_empty($suggestedDataFactory): void
    {
        $subscription = new ProductSubscription(42, new SubscriptionId('subscription-42'), []);
        $suggestedDataFactory->fromSubscription($subscription)->willReturn(null);

        $this->shouldThrow(InvalidItemException::class)->during('process', [$subscription]);
    }
}
