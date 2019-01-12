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
        $this->identifierValues[$values->productId()] = $values;
    }

    /**
     * @param int $productId
     *
     * @return ProductIdentifierValues|null
     */
    public function get(int $productId): ?ProductIdentifierValues
    {
        return $this->identifierValues[$productId] ?? null;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->identifierValues);
    }
}
