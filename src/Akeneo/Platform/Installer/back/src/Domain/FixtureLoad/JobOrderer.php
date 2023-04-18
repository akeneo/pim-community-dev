<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Domain\FixtureLoad;

final class JobOrderer
{
    public static function order(array $jobs)
    {
        usort(
            $jobs,
            function ($item1, $item2) {
                if ($item1['order'] === $item2['order']) {
                    return 0;
                }

                return ($item1['order'] < $item2['order']) ? -1 : 1;
            }
        );

        return $jobs;
    }
}
