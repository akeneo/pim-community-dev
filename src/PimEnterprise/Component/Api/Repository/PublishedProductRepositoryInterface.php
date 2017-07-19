<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Api\Repository;

/**
 * Repository interface for published product resources
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface PublishedProductRepositoryInterface
{
    /**
     * Find an object by its identifier
     *
     * @param string $identifier
     *
     * @return mixed
     */
    public function findOneByIdentifier($identifier);
}
