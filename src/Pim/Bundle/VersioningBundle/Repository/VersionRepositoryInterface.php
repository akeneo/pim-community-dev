<?php

namespace Pim\Bundle\VersioningBundle\Repository;

use Pim\Bundle\VersioningBundle\Model\Version;

/**
 * Version repository interface
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VersionRepositoryInterface
{
    /**
     * Returns all versions for a resource
     *
     * @param string $resourceName
     * @param string $resourceId
     *
     * @return Version[]|null
     */
    public function getLogEntries($resourceName, $resourceId);

    /**
     * Returns oldest (first) version for a resource
     *
     * @param string $resourceName
     * @param string $resourceId
     *
     * @return Version|null
     */
    public function getOldestLogEntry($resourceName, $resourceId);

    /**
     * Returns newest (last) version for a resource
     *
     * @param string $resourceName
     * @param string $resourceId
     *
     * @return Version|null
     */
    public function getNewestLogEntry($resourceName, $resourceId);

    /**
     * Returns pending versions
     *
     * @return Version[]|null
     */
    public function getPendingVersions();

    /**
     * Find Version entities by a set of criteria
     *
     * @param array        $criteria
     * @param array|null   $orderBy
     * @param integer|null $limit
     * @param integer|null $offset
     *
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
}
