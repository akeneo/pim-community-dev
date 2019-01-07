<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionsCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\Subscription;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\SubscriptionsCursor;
use PhpSpec\ObjectBehavior;

class SubscriptionsCursorSpec extends ObjectBehavior
{
    public function let(SubscriptionsCollection $currentPage): void
    {
        $this->beConstructedWith($currentPage);
    }

    public function it_is_an_iterator(): void
    {
        $this->shouldImplement(\Iterator::class);

        $this->shouldHaveType(SubscriptionsCursor::class);
    }

    public function it_returns_the_current_value_as_a_response_if_it_has_one(
        $currentPage,
        Subscription $subscription
    ): void {
        $currentPage->current()->willReturn($subscription);
        $subscription->getTrackerId()->willReturn(42);
        $subscription->getSubscriptionId()->willReturn('id-42');
        $subscription->getAttributes()->willReturn(
            [
                [
                    'name' => 'an_attribute',
                    'value' => 'a_value',
                ],
                [
                    'name' => 'another_attribute',
                    'value' => 'another_value',
                ],
            ]
        );
        $subscription->isMappingMissing()->willReturn(false);
        $subscription->isCancelled()->willReturn(false);

        $productSubscriptionResponse = $this->current();
        $productSubscriptionResponse->shouldBeAnInstanceOf(ProductSubscriptionResponse::class);
    }

    public function it_returns_null_if_the_current_value_is_null($currentPage): void
    {
        $currentPage->current()->willReturn(null);

        $this->current()->shouldReturn(null);
    }

    public function it_says_it_is_valid($currentPage): void
    {
        $currentPage->valid()->willReturn(true, false);

        $this->valid()->shouldReturn(true);
        $this->valid()->shouldReturn(false);
    }

    public function it_increments_the_index_and_returns_the_current_index($currentPage): void
    {
        $this->key()->shouldReturn(0);

        $currentPage->valid()->willReturn(true);
        $currentPage->hasNextPage()->willReturn(true);
        $currentPage->next()->shouldBeCalled();

        $this->next();
        $this->key()->shouldReturn(1);
    }

    public function it_loads_the_next_page_if_current_index_is_not_valid(
        $currentPage,
        SubscriptionsCollection $nextPage
    ): void {
        $currentPage->next()->shouldBeCalled();
        $currentPage->valid()->willReturn(false);
        $currentPage->hasNextPage()->willReturn(true);
        $currentPage->getNextPage()->willReturn($nextPage)->shouldBeCalled();

        $this->next();
    }

    public function it_does_not_load_the_next_page_if_current_index_is_not_valid_but_it_has_not_next_page(
        $currentPage
    ): void {
        $currentPage->next()->shouldBeCalled();
        $currentPage->valid()->willReturn(false);
        $currentPage->hasNextPage()->willReturn(false);
        $currentPage->getNextPage()->shouldNotBeCalled();

        $this->next();
    }

    public function it_rewinds_the_index($currentPage): void
    {
        $this->key()->shouldReturn(0);

        $currentPage->valid()->willReturn(true);
        $currentPage->hasNextPage()->willReturn(true);
        $currentPage->next()->shouldBeCalled();
        $currentPage->rewind()->shouldBeCalled();

        $this->next();
        $this->key()->shouldReturn(1);

        $this->rewind();
        $this->key()->shouldReturn(0);
    }
}
