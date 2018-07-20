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

namespace Akeneo\Pim\Automation\SuggestData\Component\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Represents a standard response from a subscription request
 * Holds a subscription id and optional suggested data
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductSubscriptionResponse
{
    /** @var ProductInterface */
    private $product;

    /** @var string */
    private $subscriptionId;

    /** @var array */
    private $suggestedData;

    /**
     * @param ProductInterface $product
     * @param string $subscriptionId
     * @param array $suggestedData
     */
    public function __construct(ProductInterface $product, string $subscriptionId, array $suggestedData)
    {
        $this->validate($subscriptionId, $suggestedData);

        $this->product = $product;
        $this->subscriptionId = $subscriptionId;
        $this->suggestedData = $suggestedData;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        return $this->product;
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
     * @param string $subscriptionId
     * @param array $suggestedData
     */
    private function validate(string $subscriptionId, array $suggestedData): void
    {
        if ('' === $subscriptionId) {
            throw new \InvalidArgumentException('subscription id cannot be empty');
        }
        // TODO: validate suggested data format?
    }
}
