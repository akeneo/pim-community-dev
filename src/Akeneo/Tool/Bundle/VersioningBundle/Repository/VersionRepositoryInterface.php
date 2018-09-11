<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Repository;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;

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
     * @param bool   $pending
     *
     * @return Version|null
     */
    public function getOldestLogEntry($resourceName, $resourceId, $pending);

    /**
     * Returns newest (last) version for a resource
     *
     * @param string $resourceName
     * @param string $resourceId
     * @param bool   $pending
     *
     * @return Version|null
     */
    public function getNewestLogEntry($resourceName, $resourceId, $pending = false);

    /**
     * Returns newest (last) version for an array of ressources
     *
     * @param array $resourceNames
     *
     * @return array|null
     */
    public function getNewestLogEntryForRessources($resourceNames);

    /**
     * Returns pending versions
     *
     * @param int $limit
     *
     * @return Version[]|null
     */
    public function getPendingVersions($limit = null);

    /**
     * Get total pending versions count
     *
     * @return int
     */
    public function getPendingVersionsCount();

    /**
     * Find Version entities by a set of criteria
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Find all versions potentially purgeable for given options
     *
     * @param array $options
     *
     * @return CursorInterface
     */
    public function findPotentiallyPurgeableBy(array $options = []);

    /**
     * Get the id of the most recent version for the resource name and resource ID
     *
     * @param string $resourceName
     * @param int    $resourceId
     *
     * @return int|null
     */
    public function getNewestVersionIdForResource($resourceName, $resourceId);
}
