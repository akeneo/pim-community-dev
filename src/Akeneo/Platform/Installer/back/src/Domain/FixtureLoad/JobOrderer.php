<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Domain\FixtureLoad;

final class JobOrderer
{
    /**
     * @param mixed[] $jobs
     *
     * @return mixed[]
     */
    public static function order(array $jobs): array
    {
        usort(
            $jobs,
            fn ($item1, $item2) => $item1['order'] <=> $item2['order'],
        );

        return $jobs;
    }
}
