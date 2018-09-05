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
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscription implements ProductSubscriptionInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $subscriptionId;

    /** @var SuggestedData */
    private $suggestedData;

    /** @var ProductInterface */
    private $product;

    /** @var array */
    private $rawSuggestedData;

    /**
     * @param ProductInterface $product
     * @param string $subscriptionId
     */
    public function __construct(ProductInterface $product, string $subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
        $this->product = $product;
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
     * Loads SuggestData entity from raw data if not already done
     *
     * @return SuggestedData
     */
    public function getSuggestedData(): SuggestedData
    {
        if (null === $this->suggestedData) {
            $this->suggestedData = new SuggestedData($this->rawSuggestedData);
        }

        return $this->suggestedData;
    }

    /**
     * @param SuggestedData $suggestedData
     *
     * @return ProductSubscription
     */
    public function setSuggestedData(SuggestedData $suggestedData): ProductSubscriptionInterface
    {
        $this->suggestedData = $suggestedData;
        $this->rawSuggestedData = ($suggestedData->isEmpty()) ? null : $suggestedData->getValues();

        return $this;
    }
}
