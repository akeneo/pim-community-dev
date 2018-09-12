<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionsCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionWebservice;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\Subscription;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SubscriptionsCollectionSpec extends ObjectBehavior
{
    function it_is_initializable(SubscriptionWebservice $webservice)
    {
        $this->beConstructedWith($webservice, $this->getRawFirstPage());

        $this->shouldHaveType(SubscriptionsCollection::class);
        $this->shouldImplement(\Iterator::class);
    }

    function it_rewinds_the_index(SubscriptionWebservice $webservice)
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

    function it_says_it_has_next_page_if_it_has_one(SubscriptionWebservice $webservice)
    {
        $this->beConstructedWith($webservice, $this->getRawFirstPage());

        $this->hasNextPage()->shouldReturn(true);
    }

    function it_says_it_has_not_next_page_if_it_has_not_one(SubscriptionWebservice $webservice)
    {
        $this->beConstructedWith($webservice, $this->getRawLastPage());

        $this->hasNextPage()->shouldReturn(false);
    }

    function it_returns_the_current_value_if_it_has_one(SubscriptionWebservice $webservice)
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

    function it_is_able_to_get_next_page(SubscriptionWebservice $webservice, SubscriptionsCollection $nextPage)
    {
        $this->beConstructedWith($webservice, $this->getRawFirstPage());

        $webservice->fetchProducts('/next/uri')->willReturn($nextPage)->shouldBeCalled();
        $this->getNextPage()->shouldReturn($nextPage);
    }

    function it_returns_null_if_has_not_next_page(SubscriptionWebservice $webservice)
    {
        $this->beConstructedWith($webservice, $this->getRawLastPage());

        $webservice->fetchProducts(Argument::any())->shouldNotBeCalled();

        $this->getNextPage()->shouldReturn(null);
    }

    function it_says_if_it_is_valid_or_not(SubscriptionWebservice $webservice)
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
                $attributes = $product['identifiers'] + $product['attributes'];

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
                        'brand' => 'Netgear'
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
                            'en_US' => 'Memory Card'
                        ],
                    ],
                ],
                'created_at' => '2018-08-03',
                'refreshed_at' => '2018-07-31T15',
                'valid_until' => '2019-08-03T09',
                'message' => '',
                '_links' => [
                    'self' => [
                        'href' => '/api/subscriptions/1111-aaaa'
                    ],
                    'cancel' => [
                        'href' => '/api/subscriptions/1111-aaaa',
                        'type' => 'application/prs.hal-forms+json'
                    ],
                ],
            ],
            [
                'id' => '2222-bbbb',
                'identifiers' => [
                    'upc' => '2222-bbbb',
                    'asin' => '2222-bbbb',
                    'mpn_brand' => [
                        'mpn' => '2222-bbbb',
                        'brand' => 'Frederic Fekkai'
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
                            'en_US' => 'Memory Card'
                        ],
                    ],
                ],
                'created_at' => '2018-09-06T13',
                'refreshed_at' => null,
                'valid_until' => '2019-09-06T13',
                'message' => '',
                '_links' => [
                    'self' => [
                        'href' => '/api/subscriptions/2222-bbbb'
                    ],
                    'cancel' => [
                        'href' => '/api/subscriptions/2222-bbbb',
                        'type' => 'application/prs.hal-forms+json'
                    ],
                ],
            ],
            [
                'id' => '3333-cccc',
                'identifiers' => [
                    'upc' => '3333-cccc',
                    'asin' => '3333-cccc',
                    'mpn_brand' => [
                        'mpn' => '3333-cccc',
                        'brand' => 'Netgear'
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
                            'en_US' => 'Memory Card'
                        ],
                    ],
                ],
                'created_at' => '2018-08-03',
                'refreshed_at' => '2018-07-31T15',
                'valid_until' => '2019-08-03T09',
                'message' => '',
                '_links' => [
                    'self' => [
                        'href' => '/api/subscriptions/3333-cccc'
                    ],
                    'cancel' => [
                        'href' => '/api/subscriptions/3333-cccc',
                        'type' => 'application/prs.hal-forms+json'
                    ],
                ],
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
                    'href' => '/next/uri'
                ],
            ],
            '_embedded' => [
                'subscription' => [
                    $this->getProductSubscription(1),
                    $this->getProductSubscription(2),
                ],
            ],
            'total' => 3,
            'limit' => 2
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
            'limit' => 2
        ];
    }
}
