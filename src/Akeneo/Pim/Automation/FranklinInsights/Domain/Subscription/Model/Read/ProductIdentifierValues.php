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
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;

/**
 * Read model representing the mapped identifier values of a single product.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductIdentifierValues
{
    /** @var ProductId */
    private $productId;

    /** @var array */
    private $identifierValues;

    public function __construct(ProductId $productId, array $identifierValues)
    {
        $this->productId = $productId;
        foreach (IdentifiersMapping::FRANKLIN_IDENTIFIERS as $franklinCode) {
            $this->identifierValues[$franklinCode] = $identifierValues[$franklinCode] ?? null;
        }
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    /**
     * @param string $identifier
     *
     * @return string|null
     */
    public function getValue(string $identifier): ?string
    {
        return $this->identifierValues[$identifier] ?? null;
    }

    public function hasAtLeastOneValue(): bool
    {
        return count(array_filter($this->identifierValues, function ($value) {
            return null !== $value;
        })) > 0;
    }

    public function toArray(): array
    {
        return $this->identifierValues;
    }
}
