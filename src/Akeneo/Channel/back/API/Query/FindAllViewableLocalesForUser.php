<?php

declare(strict_types=1);

namespace Akeneo\Channel\API\Query;

/**
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindAllViewableLocalesForUser
{
    /**
     * @return Locale[]
     */
    public function findAll(int $userId): array;
}
