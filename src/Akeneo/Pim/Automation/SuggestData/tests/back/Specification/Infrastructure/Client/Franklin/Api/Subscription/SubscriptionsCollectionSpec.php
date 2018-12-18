<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionsCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionWebService;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\Subscription;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SubscriptionsCollectionSpec extends ObjectBehavior
{
    public function it_is_initializable(SubscriptionWebService $webservice): void
    {
        $this->beConstructedWith($webservice, $this->getRawFirstPage());

        $this->shouldHaveType(SubscriptionsCollection::class);
        $this->shouldImplement(\Iterator::class);
    }

    public function it_rewinds_the_index(SubscriptionWebService $webservice): void
    {
        $this->beConstructedWith($webservice, $this->getRawFirstPage());

        $this->key()->shouldReturn(0);
        $this->next();
        $this->next();
        $this->next();
        $this->next();
        $this->key()->shouldReturn(4);
        $this->rewind();
        $this->key()->shouldReturn(0);
    }

    public function it_says_it_has_next_page_if_it_has_one(SubscriptionWebService $webservice): void
    {
        $this->beConstructedWith($webservice, $this->getRawFirstPage());

        $this->hasNextPage()->shouldReturn(true);
    }

    public function it_says_it_has_not_next_page_if_it_has_not_one(SubscriptionWebService $webservice): void
    {
        $this->beConstructedWith($webservice, $this->getRawLastPage());

        $this->hasNextPage()->shouldReturn(false);
    }

    public function it_returns_the_current_value_if_it_has_one(SubscriptionWebService $webservice): void
    {
        $this->beConstructedWith($webservice, $this->getRawLastPage());

        $subscription = $this->current();
        $subscription->shouldReturnAnInstanceOf(Subscription::class);
        $subscription->shouldHaveSubscriptionId('3333-cccc');
        $subscription->shouldHaveTrackerId(52);
        $subscription->shouldHaveSameAttributesAs(3);

        $this->next();
        $this->current()->shouldReturn(null);
    }

    public function it_is_able_to_get_next_page(
        SubscriptionWebService $webservice,
        SubscriptionsCollection $nextPage
    ): void {
        $this->beConstructedWith($webservice, $this->getRawFirstPage());

        $webservice->fetchProducts('/next/uri')->willReturn($nextPage)->shouldBeCalled();
        $this->getNextPage()->shouldReturn($nextPage);
    }

    public function it_returns_null_if_has_not_next_page(SubscriptionWebService $webservice): void
    {
        $this->beConstructedWith($webservice, $this->getRawLastPage());

        $webservice->fetchProducts(Argument::any())->shouldNotBeCalled();

        $this->getNextPage()->shouldReturn(null);
    }

    public function it_says_if_it_is_valid_or_not(SubscriptionWebService $webservice): void
    {
        $this->beConstructedWith($webservice, $this->getRawLastPage());

        $this->valid()->shouldReturn(true);
        $this->next();
        $this->valid()->shouldReturn(false);
    }

    public function getMatchers()
    {
        return [
            'haveSubscriptionId' => function (Subscription $subscription, $id) {
                return $subscription->getSubscriptionId() === $id;
            },
            'haveTrackerId' => function (Subscription $subscription, $id) {
                return $subscription->getTrackerId() === $id;
            },
            'haveSameAttributesAs' => function (Subscription $subscription, $productId) {
                $product = $this->getProductSubscription($productId);
                $attributes = array_merge($product['mapped_identifiers'], $product['mapped_attributes']);

                return $subscription->getAttributes() === $attributes;
            },
        ];
    }

    /**
     * @param int $numberOfTheSubscription
     *
     * @return array
     */
    private function getProductSubscription(int $numberOfTheSubscription): array
    {
        $productSubscriptions = [
            [
                'id' => '1111-aaaa',
                'identifiers' => [
                    'upc' => '1111-aaaa',
                    'asin' => '1111-aaaa',
                    'mpn_brand' => [
                        'mpn' => '1111-aaaa',
                        'brand' => 'Netgear',
                    ],
                ],
                'attributes' => [
                    'Memory' => '256 MB',
                    'Series' => 'R7000',
                    'General' => 'Color Black',
                ],
                'extra' => [
                    'tracker_id' => '42',
                    'family' => [
                        'code' => 'memory_card',
                        'label' => [
                            'en_US' => 'Memory Card',
                        ],
                    ],
                ],
                'created_at' => '2018-08-03',
                'refreshed_at' => '2018-07-31T15',
                'valid_until' => '2019-08-03T09',
                'message' => '',
                '_links' => [
                    'self' => [
                        'href' => '/api/subscriptions/1111-aaaa',
                    ],
                    'cancel' => [
                        'href' => '/api/subscriptions/1111-aaaa',
                        'type' => 'application/prs.hal-forms+json',
                    ],
                ],
                'mapped_identifiers' => [
                    [
                        'name' => 'pim_upc',
                        'value' => '1111-aaaa',
                    ],
                ],
                'mapped_attributes' => [],
                'misses_mapping' => true,
            ],
            [
                'id' => '2222-bbbb',
                'identifiers' => [
                    'upc' => '2222-bbbb',
                    'asin' => '2222-bbbb',
                    'mpn_brand' => [
                        'mpn' => '2222-bbbb',
                        'brand' => 'Frederic Fekkai',
                    ],
                ],
                'attributes' => [
                    'Memory' => '256 MB',
                    'Series' => 'R7000',
                ],
                'extra' => [
                    'tracker_id' => '50',
                    'family' => [
                        'code' => 'memory_card',
                        'label' => [
                            'en_US' => 'Memory Card',
                        ],
                    ],
                ],
                'created_at' => '2018-09-06T13',
                'refreshed_at' => null,
                'valid_until' => '2019-09-06T13',
                'message' => '',
                '_links' => [
                    'self' => [
                        'href' => '/api/subscriptions/2222-bbbb',
                    ],
                    'cancel' => [
                        'href' => '/api/subscriptions/2222-bbbb',
                        'type' => 'application/prs.hal-forms+json',
                    ],
                ],
                'mapped_identifiers' => [],
                'mapped_attributes' => [],
                'misses_mapping' => false,
            ],
            [
                'id' => '3333-cccc',
                'identifiers' => [
                    'upc' => '3333-cccc',
                    'asin' => '3333-cccc',
                    'mpn_brand' => [
                        'mpn' => '3333-cccc',
                        'brand' => 'Netgear',
                    ],
                ],
                'attributes' => [
                    'Memory' => '512 MB',
                    'Series' => 'R7000',
                    'General' => 'Color Pink',
                ],
                'extra' => [
                    'tracker_id' => '52',
                    'family' => [
                        'code' => 'memory_card',
                        'label' => [
                            'en_US' => 'Memory Card',
                        ],
                    ],
                ],
                'created_at' => '2018-08-03',
                'refreshed_at' => '2018-07-31T15',
                'valid_until' => '2019-08-03T09',
                'message' => '',
                '_links' => [
                    'self' => [
                        'href' => '/api/subscriptions/3333-cccc',
                    ],
                    'cancel' => [
                        'href' => '/api/subscriptions/3333-cccc',
                        'type' => 'application/prs.hal-forms+json',
                    ],
                ],
                'mapped_identifiers' => [
                    [
                        'name' => 'pim_upc',
                        'value' => '3333-cccc',
                    ],
                ],
                'mapped_attributes' => [
                    [
                        'name' => 'memory',
                        'value' => '512 MEGABYTE',
                    ],
                    [
                        'name' => 'series',
                        'value' => 'R7000',
                    ],
                ],
                'misses_mapping' => false,
            ],
        ];

        return $productSubscriptions[$numberOfTheSubscription - 1];
    }

    /**
     * @return array
     */
    private function getRawFirstPage(): array
    {
        return [
            '_links' => [
                'subscription' => [],
                'next' => [
                    'href' => '/next/uri',
                ],
            ],
            '_embedded' => [
                'subscription' => [
                    $this->getProductSubscription(1),
                    $this->getProductSubscription(2),
                ],
            ],
            'total' => 3,
            'limit' => 2,
        ];
    }

    /**
     * @return array
     */
    private function getRawLastPage(): array
    {
        return [
            '_links' => [
                'subscription' => [],
            ],
            '_embedded' => [
                'subscription' => [
                    $this->getProductSubscription(3),
                ],
            ],
            'total' => 3,
            'limit' => 2,
        ];
    }
}
