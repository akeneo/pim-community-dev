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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Represents a standard response from a subscription request
 * Holds a subscription id and optional suggested data
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductSubscriptionResponse
{
    /** @var int */
    private $productId;

    /** @var string */
    private $subscriptionId;

    /** @var array */
    private $suggestedData;

    /**
     * @param int $productId
     * @param string $subscriptionId
     * @param array $suggestedData
     */
    public function __construct(int $productId, string $subscriptionId, array $suggestedData)
    {
        $this->validate($productId, $subscriptionId, $suggestedData);

        $this->productId = $productId;
        $this->subscriptionId = $subscriptionId;
        $this->suggestedData = $suggestedData;
    }

    /**
     * @return integer
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function getSubscriptionId(): string
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
     * @param int $productId
     * @param string $subscriptionId
     * @param array $suggestedData
     */
    private function validate(int $productId, string $subscriptionId, array $suggestedData): void
    {
        // TODO: Validate $productId

        if ('' === $subscriptionId) {
            throw new \InvalidArgumentException('subscription id cannot be empty');
        }
        // TODO: validate suggested data format?
    }
}
