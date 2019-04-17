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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\ProductSubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRepositorySpec extends ObjectBehavior
{
    public function let(EntityManagerInterface $em): void
    {
        $this->beConstructedWith($em);
    }

    public function it_is_a_product_subscription_repository(): void
    {
        $this->shouldBeAnInstanceOf(ProductSubscriptionRepository::class);
    }

    public function it_implements_product_subscription_repository_interface(): void
    {
        $this->shouldImplement(ProductSubscriptionRepositoryInterface::class);
    }

    public function it_deletes_subscription($em, ProductSubscription $subscription): void
    {
        $em->remove($subscription)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->delete($subscription)->shouldReturn(null);
    }

    public function it_bulk_save_subscriptions($em): void
    {
        $subscription = new ProductSubscription(new ProductId(42), new SubscriptionId('fake-id-42'), ['asin' => 'ABC']);
        $subscription2 = new ProductSubscription(new ProductId(43), new SubscriptionId('fake-id-43'), ['asin' => '123']);

        $em->persist($subscription)->shouldBeCalled();
        $em->persist($subscription2)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->bulkSave([$subscription, $subscription2]);
    }

    public function it_bulk_delete_subscriptions($em): void
    {
        $subscription = new ProductSubscription(new ProductId(42), new SubscriptionId('fake-id-42'), ['asin' => 'ABC']);
        $subscription2 = new ProductSubscription(new ProductId(43), new SubscriptionId('fake-id-43'), ['asin' => '123']);

        $em->remove($subscription)->shouldBeCalled();
        $em->remove($subscription2)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->bulkDelete([$subscription, $subscription2]);
    }
}
