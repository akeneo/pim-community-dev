<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;

final class ProductInfosForSubscription
{
    /** @var ProductId */
    private $productId;

    /** @var ProductIdentifierValues */
    private $productIdentifierValues;

    /** @var Family|null */
    private $family;

    /** @var string */
    private $identifier;

    /** @var bool */
    private $isVariant;

    /** @var bool */
    private $isSubscribed;

    public function __construct(
        ProductId $productId,
        ProductIdentifierValues $productIdentifierValues,
        ?Family $family,
        string $identifier,
        bool $isVariant,
        bool $isSubscribed
    ) {
        $this->productId = $productId;
        $this->productIdentifierValues = $productIdentifierValues;
        $this->family = $family;
        $this->identifier = $identifier;
        $this->isVariant = $isVariant;
        $this->isSubscribed = $isSubscribed;
    }

    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    public function getFamily(): ?Family
    {
        return $this->family;
    }

    public function getProductIdentifierValues(): ProductIdentifierValues
    {
        return $this->productIdentifierValues;
    }

    public function hasFamily(): bool
    {
        return null !== $this->family;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function isVariant(): bool
    {
        return $this->isVariant;
    }

    public function isSubscribed(): bool
    {
        return $this->isSubscribed;
    }
}
