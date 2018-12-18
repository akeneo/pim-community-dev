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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\InMemory;

use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\EmptySuggestedDataQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\ValueObject\SuggestedData;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\InMemory\EmptySuggestedDataQuery;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Memory\InMemoryProductSubscriptionRepository;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class EmptySuggestedDataQuerySpec extends ObjectBehavior
{
    public function let(InMemoryProductSubscriptionRepository $subscriptionRepository): void
    {
        $this->beConstructedWith($subscriptionRepository);
    }

    public function it_is_an_empty_suggested_data_query(): void
    {
        $this->shouldHaveType(EmptySuggestedDataQuery::class);
    }

    public function it_implements_empty_suggested_data_query_interface(): void
    {
        $this->shouldImplement(EmptySuggestedDataQueryInterface::class);
    }

    public function it_empty_suggested_data_for_all_subscriptions(
        $subscriptionRepository
    ): void {
        $rawSuggestedData1 = [['pimAttributeCode' => 'asin', 'value' => 'my_asin-001']];
        $subscription1 = new ProductSubscription(1, 'test-001', []);
        $subscription1->setSuggestedData(new SuggestedData($rawSuggestedData1));

        $rawSuggestedData2 = [['pimAttributeCode' => 'upc', 'value' => 'my-upc-002']];
        $subscription2 = new ProductSubscription(2, 'test-002', []);
        $subscription2->setSuggestedData(new SuggestedData($rawSuggestedData2));

        Assert::same($rawSuggestedData1, $subscription1->getSuggestedData()->getRawValues());
        Assert::same($rawSuggestedData2, $subscription2->getSuggestedData()->getRawValues());

        $subscriptionRepository->getSubscriptions()->willReturn([$subscription1, $subscription2]);

        $this->execute();
        Assert::null($subscription1->getSuggestedData()->getRawValues());
        Assert::null($subscription2->getSuggestedData()->getRawValues());
    }
}
