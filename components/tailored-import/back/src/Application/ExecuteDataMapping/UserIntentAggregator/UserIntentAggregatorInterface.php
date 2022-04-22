<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\UserIntentAggregator;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;

interface UserIntentAggregatorInterface
{
    /**
     * @param array<ValueUserIntent> $userIntents
     *
     * @return array<ValueUserIntent>
     */
    public function aggregateByTarget(array $userIntents): array;
}
