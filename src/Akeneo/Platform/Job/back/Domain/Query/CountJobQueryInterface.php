<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Domain\Query;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface CountJobQueryInterface
{
    public function all(): int;
}
