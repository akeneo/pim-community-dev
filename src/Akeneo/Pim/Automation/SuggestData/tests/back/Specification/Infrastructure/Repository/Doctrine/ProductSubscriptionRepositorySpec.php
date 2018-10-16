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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Repository\Doctrine\ProductSubscriptionRepository;
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
}
