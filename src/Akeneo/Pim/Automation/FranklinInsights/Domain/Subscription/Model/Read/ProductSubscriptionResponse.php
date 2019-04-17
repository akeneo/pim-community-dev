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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;

/**
 * Represents a standard response from a subscription request
 * Holds a subscription id and optional suggested data.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductSubscriptionResponse
{
    /** @var ProductId */
    private $productId;

    /** @var SubscriptionId */
    private $subscriptionId;

    /** @var array */
    private $suggestedData;

    /** @var bool */
    private $isMappingMissing;

    /** @var bool */
    private $isCancelled;

    public function __construct(
        ProductId $productId,
        SubscriptionId $subscriptionId,
        array $suggestedData,
        bool $isMappingMissing,
        bool $isCancelled
    ) {
        $this->productId = $productId;
        $this->subscriptionId = $subscriptionId;
        $this->suggestedData = $suggestedData;
        $this->isMappingMissing = $isMappingMissing;
        $this->isCancelled = $isCancelled;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    /**
     * @return SubscriptionId
     */
    public function getSubscriptionId(): SubscriptionId
    {
        return $this->subscriptionId;
    }

    /**
     * @return array
     */
    public function getSuggestedData(): array
    {
        return $this->suggestedData;
    }

    /**
     * @return bool
     */
    public function isMappingMissing(): bool
    {
        return $this->isMappingMissing;
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }
}
