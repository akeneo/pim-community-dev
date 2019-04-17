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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;

/**
 * Collection of ProductIdentifierValues read models
 * Stores the mapped identifier values for several products.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductIdentifierValuesCollection
{
    /** @var ProductIdentifierValues */
    private $identifierValues = [];

    /**
     * @param ProductIdentifierValues $values
     */
    public function add(ProductIdentifierValues $values): void
    {
        $this->identifierValues[$values->productId()->toInt()] = $values;
    }

    /**
     * @param ProductId $productId
     *
     * @return ProductIdentifierValues|null
     */
    public function get(ProductId $productId): ?ProductIdentifierValues
    {
        return $this->identifierValues[$productId->toInt()] ?? null;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->identifierValues);
    }
}
