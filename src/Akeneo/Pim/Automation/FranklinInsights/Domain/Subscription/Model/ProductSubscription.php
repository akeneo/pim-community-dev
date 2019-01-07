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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SuggestedData;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscription
{
    /** @var int */
    private $id;

    /** @var string */
    private $subscriptionId;

    /** @var SuggestedData */
    private $suggestedData;

    /** @var int */
    private $productId;

    /** @var array */
    private $rawSuggestedData;

    /** @var bool */
    private $isMappingMissing;

    /** @var string */
    private $requestedAsin;

    /** @var string */
    private $requestedUpc;

    /** @var string */
    private $requestedBrand;

    /** @var string */
    private $requestedMpn;

    /**
     * @param int $productId
     * @param string $subscriptionId
     * @param array $productIdentifiers
     */
    public function __construct(int $productId, string $subscriptionId, array $productIdentifiers)
    {
        $this->productId = $productId;
        $this->subscriptionId = $subscriptionId;
        $this->isMappingMissing = false;

        $this->fillProductIdentifiers($productIdentifiers);
    }

    /**
     * @return int
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
     * Loads SuggestedData entity from raw data if not already done.
     *
     * @return SuggestedData
     */
    public function getSuggestedData(): SuggestedData
    {
        if (null === $this->suggestedData) {
            $this->suggestedData = new SuggestedData($this->rawSuggestedData ?? []);
        }

        return $this->suggestedData;
    }

    /**
     * @param SuggestedData $suggestedData
     *
     * @return ProductSubscription
     */
    public function setSuggestedData(SuggestedData $suggestedData): self
    {
        $this->suggestedData = $suggestedData;
        $this->rawSuggestedData = $suggestedData->getRawValues();

        return $this;
    }

    /**
     * @param bool $isMappingMissing
     *
     * @return ProductSubscription
     */
    public function markAsMissingMapping(bool $isMappingMissing): self
    {
        $this->isMappingMissing = $isMappingMissing;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMappingMissing(): bool
    {
        return $this->isMappingMissing;
    }

    /**
     * Returns non-empty requested identifier values, i.e the ones used in the subscription request.
     *
     * @return array
     */
    public function requestedIdentifierValues(): array
    {
        return array_filter(
            [
                'asin' => $this->requestedAsin,
                'upc' => $this->requestedUpc,
                'brand' => $this->requestedBrand,
                'mpn' => $this->requestedMpn,
            ]
        );
    }

    /**
     * @param array $productIdentifiers
     */
    private function fillProductIdentifiers(array $productIdentifiers): void
    {
        $this->requestedAsin = $productIdentifiers['asin'] ?? null;
        $this->requestedUpc = $productIdentifiers['upc'] ?? null;
        $this->requestedMpn = $productIdentifiers['mpn'] ?? null;
        $this->requestedBrand = $productIdentifiers['brand'] ?? null;
    }
}
