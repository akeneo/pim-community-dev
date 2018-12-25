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

/**
 * Fetches current product family id directly from the DB.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
interface SelectProductFamilyIdQueryInterface
{
    /**
     * @param int $productId
     *
     * @return int|null
     */
    public function execute(int $productId): ?int;
}
