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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\Product;

use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductIdentifierValues;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
interface SelectProductIdentifierValuesQueryInterface
{
    /**
     * Retrieves mapped identifer values for a given product.
     *
     * @param int $productId
     *
     * @return ProductIdentifierValues|null
     */
    public function execute(int $productId): ?ProductIdentifierValues;
}
