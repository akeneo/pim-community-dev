<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Analytics;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ApiConnectionCountQuery
{
    public function fetch(): array;
}
