<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\Subscription;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\SubscriptionCollection;
use PhpSpec\ObjectBehavior;

class SubscriptionCollectionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith($this->buildApiResponse());
    }

    public function it_is_subscription_collection(): void
    {
        $this->shouldHaveType(SubscriptionCollection::class);
    }

    public function it_is_iterable(): void
    {
        $this->shouldImplement(\IteratorAggregate::class);
    }

    public function it_returns_a_collection_of_subscription(): void
    {
        $subscriptions = $this->getIterator()->getArrayCopy();
        $subscriptions->shouldBeArray();
        $subscriptions->shouldHaveCount(2);
        $subscriptions[0]->shouldBeAnInstanceOf(Subscription::class);
        $subscriptions[1]->shouldBeAnInstanceOf(Subscription::class);
    }

    public function it_returns_the_first_subscription(): void
    {
        $this->first()->shouldReturnAnInstanceOf(Subscription::class);
    }

    public function it_throws_an_exception_if_the_validation_fails(): void
    {
        $subscriptions = [
            '_embedded' => [
                'subscription' => 'invalid value',
            ],
        ];
        $this->beConstructedWith($subscriptions);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
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
                ],
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
                    'label' => ['en_US' => 'Laptop'],
                ],
            ],
            'mapped_identifiers' => [
                [
                    'name' => 'ean',
                    'value' => '606449099812',
                ],
            ],
            'mapped_attributes' => [
                [
                    'name' => 'ram',
                    'value' => '256 MEGABYTE',
                ],
            ],
            'misses_mapping' => false,
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
                'tracker_id' => 50,
            ],
            'mapped_identifiers' => [
                [
                    'name' => 'ean',
                    'value' => '123456789123',
                ],
            ],
            'mapped_attributes' => [
                [
                    'name' => 'processor_frequency',
                    'value' => '1 GIGAHERTZ',
                ],
            ],
            'misses_mapping' => false,
        ];
    }
}
