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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Query;

/**
 * Checks if a product was subscribed to Franklin.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
interface GetSubscriptionStatusForProductInterface
{
    /**
     * @param int $productId
     *
     * @return mixed
     */
    public function query(int $productId): bool;
}
