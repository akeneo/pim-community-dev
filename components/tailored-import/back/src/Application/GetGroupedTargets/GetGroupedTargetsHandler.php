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

namespace Akeneo\Platform\TailoredImport\Application\GetGroupedTargets;

class GetGroupedTargetsHandler
{
    public function __construct(
        private GetGroupedTargetsInterface $getGroupedTargets,
    ) {
    }

    public function handle(GetGroupedTargetsQuery $query): GroupedTargetsResult
    {
        return $this->getGroupedTargets->get($query);
    }
}
