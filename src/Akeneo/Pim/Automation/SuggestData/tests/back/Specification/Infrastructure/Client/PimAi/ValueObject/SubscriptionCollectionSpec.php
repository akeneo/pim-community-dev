<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\Subscription;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;
use PhpSpec\ObjectBehavior;

class SubscriptionCollectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith($this->buildApiResponse());
    }

    public function it_is_subscription_collection()
    {
        $this->shouldHaveType(SubscriptionCollection::class);
    }

    public function it_returns_a_collection_of_subscription()
    {
        $subscriptions = $this->getSubscriptions();
        $subscriptions->shouldBeArray();
        $subscriptions->shouldHaveCount(2);
        $subscriptions[0]->shouldBeAnInstanceOf(Subscription::class);
        $subscriptions[1]->shouldBeAnInstanceOf(Subscription::class);
    }

    public function it_returns_the_first_subscription()
    {
        $this->getFirst()->shouldReturnAnInstanceOf(Subscription::class);
    }

    public function it_throws_an_exception_if_the_validation_fails()
    {
        $subscriptions = [
            '_embedded' => [
                'subscription' => 'invalid value',
            ],
        ];
        $this->beConstructedWith($subscriptions);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_is_countable()
    {
        return $this->shouldImplement(\Countable::class);
    }

    private function buildApiResponse()
    {
        return [
            '_links' => [
                0 => [
                    'href' => '/api/subscriptions/86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
                ],
                1 => [
                    'href' => '/api/subscriptions/a3fd0f30-c689-4a9e-84b4-7eac1f661923',
                ],
            ],
            '_embedded' => [
                'subscription' => [
                    0 => $this->buildFirstSubscription(),
                    1 => $this->buildSecondSubscription(),
                ]
            ],
        ];
    }

    private function buildFirstSubscription()
    {
        return [
            'id' => '86b7a527-9531-4a46-bc5c-02d89dcbc7eb',
            'identifiers' => [
                'upc' => '606449099812',
            ],
            'attributes' => [
                'Memory' => 'RAM (Installed): 256 MB',
            ],
            'extra' => [
                'tracker_id' => 42,
                'family' => [
                    'code' => 'laptops',
                    'label' => ['en_US' => 'Laptop']
                ]
            ]
        ];
    }

    private function buildSecondSubscription()
    {
        return [
            'id' => 'a3fd0f30-c689-4a9e-84b4-7eac1f661923',
            'identifiers' => [
                'upc' => '123456789123',
            ],
            'attributes' => [
                'Processor' => '1 GHz',
            ],
            'extra' => [
                'tracker_id' => 50
            ]
        ];
    }
}
