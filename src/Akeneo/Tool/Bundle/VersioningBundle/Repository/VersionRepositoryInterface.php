<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Repository;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Ramsey\Uuid\UuidInterface;

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
     * @return Version[]|null
     */
    public function getLogEntries(string $resourceName, ?string $resourceId, ?UuidInterface $resourceUuid, ?int $limit = null);

    /**
     * Returns oldest (first) version for a resource
     *
     * @param string $resourceName
     * @param string|null $resourceId
     * @param bool   $pending
     *
     * @return Version|null
     */
    public function getOldestLogEntry($resourceName, $resourceId, ?UuidInterface $resourceUuid, $pending);

    /**
     * Returns newest (last) version for a resource
     *
     * @param string $resourceName
     * @param string|null $resourceId
     * @param bool   $pending
     *
     * @return Version|null
     */
    public function getNewestLogEntry($resourceName, $resourceId, ?UuidInterface $resourceUuid, $pending = false);

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
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null);

    /**
     * @deprecated Will be removed in 4.0
     *
     * Find all versions potentially purgeable for given options
     *
     * @param array $options
     *
     * @return CursorInterface|\PDOStatement
     */
    public function findPotentiallyPurgeableBy(array $options = []);
}
