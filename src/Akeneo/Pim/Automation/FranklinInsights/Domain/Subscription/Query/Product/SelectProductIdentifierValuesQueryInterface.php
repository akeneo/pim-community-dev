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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductIdentifierValuesCollection;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
interface SelectProductIdentifierValuesQueryInterface
{
    /**
     * Retrieves mapped identifer values for given products.
     *
     * @param int[] $productIds
     *
     * @return ProductIdentifierValuesCollection
     */
    public function execute(array $productIds): ProductIdentifierValuesCollection;
}
