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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Writer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SuggestedDataWriterSpec extends ObjectBehavior
{
    public function let(
        ProductSubscriptionRepositoryInterface $subscriptionRepository,
        EntityManagerInterface $entityManager
    ): void {
        $this->beConstructedWith($subscriptionRepository, $entityManager);
    }

    public function it_is_a_writer(): void
    {
        $this->shouldImplement(ItemWriterInterface::class);
    }

    public function it_writes_and_deletes_subscriptions($subscriptionRepository, $entityManager): void
    {
        $subscription1 = new ProductSubscription(11, 'subscription-11', []);
        $subscription2 = new ProductSubscription(12, 'subscription-12', []);
        $subscription2->markAsCancelled();
        $subscription3 = new ProductSubscription(13, 'subscription-13', []);

        $subscriptionRepository->bulkDelete([$subscription2])->shouldBeCalled();
        $subscriptionRepository->bulkSave([$subscription1, $subscription3])->shouldBeCalled();

        $this->write([$subscription1, $subscription2, $subscription3]);

        $entityManager->clear()->shouldBeCalled();
    }
}
