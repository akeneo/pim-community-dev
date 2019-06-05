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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValues;

/**
 * Holds the information needed to subscribe a product.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductSubscriptionRequest
{
    /** @var ProductId */
    private $productId;

    /** @var Family */
    private $family;

    /** @var ProductIdentifierValues */
    private $productIdentifierValues;

    /** @var string */
    private $productIdentifier;

    public function __construct(
        ProductId $productId,
        Family $family,
        ProductIdentifierValues $productIdentifierValues,
        string $productIdentifier
    ) {
        $this->productId = $productId;
        $this->family = $family;
        $this->productIdentifierValues = $productIdentifierValues;
        $this->productIdentifier = $productIdentifier;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getProductIdentifier(): string
    {
        return $this->productIdentifier;
    }

    public function getFamily(): Family
    {
        return $this->family;
    }

    public function getMappedValues(): array
    {
        $mappedValues = array_filter($this->productIdentifierValues->toArray(), function ($value) {
            return null !== $value;
        });

        return $this->doNotKeepMpnOrBrandAlone($mappedValues);
    }

    /**
     * For Franklin, MPN and Brand form one identifier.
     * As a result, we should never subscribe a product if it has a value for only one of them.
     *
     * @param array $mapped
     *
     * @return array
     */
    private function doNotKeepMpnOrBrandAlone(array $mapped): array
    {
        if (!array_key_exists('mpn', $mapped) || !array_key_exists('brand', $mapped)) {
            unset($mapped['mpn'], $mapped['brand']);
        }

        return $mapped;
    }
}
