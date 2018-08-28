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
    public function it_is_a_product_subscription_repository()
    {
        $this->shouldImplement(ProductSubscriptionRepositoryInterface::class);
    }

    public function it_is_the_in_memory_implementation_of_the_product_subscription_repository()
    {
        $this->shouldBeAnInstanceOf(InMemoryProductSubscriptionRepository::class);
    }

    public function it_find_a_subscription_by_its_product_and_subscription_id()
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', []);
        $this->save($subscription);

        $this
            ->findOneByProductAndSubscriptionId($product, 'a-fake-subscription')
            ->shouldReturn($subscription);
    }

    public function it_find_no_subscription_if_subscription_id_does_not_exists()
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', []);
        $this->save($subscription);

        $this
            ->findOneByProductAndSubscriptionId($product, 'another-fake-subscription')
            ->shouldReturn(null);
    }

    public function it_find_no_subscription_if_product_was_not_subscribed()
    {
        $product = new Product();
        $product->setId(42);

        $this
            ->findOneByProductAndSubscriptionId($product, 'a-fake-subscription')
            ->shouldReturn(null);
    }

    public function it_saves_a_product_subscription()
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', []);
        $this->save($subscription);

        $this->shouldHaveProductSubscription($subscription);
    }

    public function it_returns_a_product_subscription_status_for_a_product_id()
    {
        $product = new Product();
        $product->setId(42);
        $subscription = new ProductSubscription($product, 'a-fake-subscription', []);
        $this->save($subscription);

        $this->getSubscriptionStatusForProductId(42)->shouldReturn(['subscription_id' => 'a-fake-subscription']);
        $this->getSubscriptionStatusForProductId(43)->shouldReturn(['subscription_id' => '']);
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
