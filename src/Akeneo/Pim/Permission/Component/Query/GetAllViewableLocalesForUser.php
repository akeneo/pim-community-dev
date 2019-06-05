<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Query;

interface GetAllViewableLocalesForUser
{
    /**
     * @return string[] viewable locale codes
     */
    public function fetchAll(int $userId): array;
}
