<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Repository;

/**
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface AttributeRepositoryInterface
{
    /**
     * Find codes of attributes usable in grid.
     *
     * @param array|null $groupIds
     *
     * @return array
     */
    public function findAttributeCodesUseableInGrid($groupIds = null);

    /**
     * Find ALL attribute codes.
     *
     * @return array
     */
    public function findAttributeCodes();
}
