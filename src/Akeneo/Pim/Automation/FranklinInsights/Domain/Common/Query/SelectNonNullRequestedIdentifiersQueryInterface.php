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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query;

/**
 * Selects non null requested identifier values from subscriptions, indexed by product id.
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
interface SelectNonNullRequestedIdentifiersQueryInterface
{
    /**
     * @param array $updatedIdentifiers
     * @param int $searchAfter
     * @param int $limit
     *
     * @return array
     */
    public function execute(array $updatedIdentifiers, int $searchAfter, int $limit): array;
}
