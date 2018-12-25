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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\InternalApi\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionStatus;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProductSubscriptionStatusNormalizer
{
    /**
     * @param ProductSubscriptionStatus $productSubscriptionStatus
     *
     * [
     *     'isConnectionActive' => (bool),
     *     'isIdentifierMappingValid' => (bool),
     *     'isSubscribed' => (bool),
     *     'hasFamily' => (bool),
     *     'fillsMapping' => (bool),
     * ]
     *
     * @return array
     */
    public function normalize(ProductSubscriptionStatus $productSubscriptionStatus): array
    {
        $connectionStatus = $productSubscriptionStatus->getConnectionStatus();

        return [
            'isConnectionActive' => $connectionStatus->isActive(),
            'isIdentifiersMappingValid' => $connectionStatus->isIdentifiersMappingValid(),
            'isSubscribed' => $productSubscriptionStatus->isSubscribed(),
            'hasFamily' => $productSubscriptionStatus->hasFamily(),
            'isMappingFilled' => $productSubscriptionStatus->isMappingFilled(),
            'isProductVariant' => $productSubscriptionStatus->isProductVariant(),
        ];
    }
}
