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

namespace Akeneo\Pim\Permission\Component\Query;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
interface GetAccessGroupIdsForLocaleCode
{
    /**
     * Get group ids that have the specified access to a locale code.
     *
     * @param string $localeCode
     * @param string $accessLevel
     *
     * @return int[]
     */
    public function getGrantedUserGroupIdsForLocaleCode(string $localeCode, string $accessLevel): array;
}
