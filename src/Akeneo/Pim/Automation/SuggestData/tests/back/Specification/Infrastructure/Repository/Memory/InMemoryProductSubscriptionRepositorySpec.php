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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Memory\InMemoryProductSubscriptionRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class InMemoryProductSubscriptionRepositorySpec extends ObjectBehavior
{
    function it_is_a_product_subscription_repository()
    {
        $this->shouldImplement(ProductSubscriptionRepositoryInterface::class);
    }

    function it_is_the_in_memory_implementation_of_the_product_subscription_repository()
    {
        $this->shouldBeAnInstanceOf(InMemoryProductSubscriptionRepository::class);
    }

    function it_find_a_subscription_by_its_product_and_subscription_id()
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', []);
        $this->save($subscription);

        $this
            ->findOneByProductAndSubscriptionId($product, 'a-fake-subscription')
            ->shouldReturn($subscription);
    }

    function it_find_no_subscription_if_subscription_id_does_not_exists()
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', []);
        $this->save($subscription);

        $this
            ->findOneByProductAndSubscriptionId($product, 'another-fake-subscription')
            ->shouldReturn(null);
    }

    function it_find_no_subscription_if_product_was_not_subscribed()
    {
        $product = new Product();
        $product->setId(42);

        $this
            ->findOneByProductAndSubscriptionId($product, 'a-fake-subscription')
            ->shouldReturn(null);
    }

    function it_saves_a_product_subscription()
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', []);
        $this->save($subscription);

        $this->shouldHaveProductSubscription($subscription);
    }

    function it_finds_a_product_subscription_in_terms_of_a_product_id()
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', []);
        $this->save($subscription);

        $this->findOneByProductId(42)->shouldReturn($subscription);
    }

    function it_returns_null_if_you_asked_for_a_product_without_subscription()
    {
        $this->findOneByProductId(42)->shouldReturn(null);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchers(): array
    {
        return array(
            'haveProductSubscription' => function (
                InMemoryProductSubscriptionRepository $testedRepository,
                ProductSubscription $expectedSubscription
            ) {
                $product = $expectedSubscription->getProduct();

                return $expectedSubscription === $testedRepository->findOneByProductAndSubscriptionId(
                        $product,
                        'a-fake-subscription'
                    );
            },
        );
    }
}
