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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;
use PhpSpec\ObjectBehavior;

class ProductSubscriptionStatusNormalizerSpec extends ObjectBehavior
{
    public function it_normalizes_a_product_subscription_status(): void
    {
        $connectionStatus = new ConnectionStatus(true, true, true, 42);
        $productSubscriptionStatus = new ProductSubscriptionStatus(
            $connectionStatus,
            true,
            true,
            true,
            true
        );

        $this->normalize($productSubscriptionStatus)->shouldReturn([
            'isConnectionActive' => true,
            'isIdentifiersMappingValid' => true,
            'isSubscribed' => true,
            'hasFamily' => true,
            'isMappingFilled' => true,
            'isProductVariant' => true,
        ]);

        $connectionStatus = new ConnectionStatus(false, false, false, 42);
        $productSubscriptionStatus = new ProductSubscriptionStatus(
            $connectionStatus,
            false,
            false,
            false,
            false
        );

        $this->normalize($productSubscriptionStatus)->shouldReturn([
            'isConnectionActive' => false,
            'isIdentifiersMappingValid' => false,
            'isSubscribed' => false,
            'hasFamily' => false,
            'isMappingFilled' => false,
            'isProductVariant' => false,
        ]);
    }
}
