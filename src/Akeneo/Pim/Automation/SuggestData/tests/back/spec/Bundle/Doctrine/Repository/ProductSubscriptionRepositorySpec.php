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

namespace spec\Akeneo\Pim\Automation\SuggestData\tests\back\spec\Bundle\Doctrine\Repository;

use Akeneo\Pim\Automation\SuggestData\Bundle\Doctrine\Repository\ProductSubscriptionRepository;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ProductSubscriptionRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionRepositorySpec extends ObjectBehavior
{
    function it_is_a_product_subscription_repository(ObjectManager $em)
    {
        $this->beConstructedWith($em, ProductSubscription::class);
        $this->shouldHaveType(ProductSubscriptionRepository::class);
        $this->shouldImplement(ProductSubscriptionRepositoryInterface::class);
    }
}
