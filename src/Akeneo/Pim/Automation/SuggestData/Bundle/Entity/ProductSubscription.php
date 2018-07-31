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

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Entity;

use Akeneo\Pim\Automation\SuggestData\Component\Model\ProductSubscriptionInterface;
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

    /** @var array */
    private $suggestedData;

    /** @var ProductInterface */
    private $product;

    /**
     * @param ProductInterface $product
     * @param string $subscriptionId
     * @param array|null $suggestedData
     */
    public function __construct(ProductInterface $product, string $subscriptionId, array $suggestedData = [])
    {
        $this->subscriptionId = $subscriptionId;
        $this->product = $product;
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
     * @param array $suggestedData
     *
     * @return ProductSubscription
     */
    public function setSuggestedData(array $suggestedData): ProductSubscriptionInterface
    {
        $this->suggestedData = $suggestedData;

        return $this;
    }
}
