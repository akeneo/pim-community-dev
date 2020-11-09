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
    public function getLogEntries(string $resourceName, string $resourceId): ?array;

    /**
     * Returns oldest (first) version for a resource
     *
     * @param string $resourceName
     * @param string $resourceId
     * @param bool   $pending
     */
    public function getOldestLogEntry(string $resourceName, string $resourceId, bool $pending): ?Version;

    /**
     * Returns newest (last) version for a resource
     *
     * @param string $resourceName
     * @param string $resourceId
     * @param bool   $pending
     */
    public function getNewestLogEntry(string $resourceName, string $resourceId, bool $pending = false): ?Version;

    /**
     * Returns newest (last) version for an array of ressources
     *
     * @param array $resourceNames
     *
     * @return array|null
     */
    public function getNewestLogEntryForRessources(array $resourceNames): ?array;

    /**
     * Returns pending versions
     *
     * @param int $limit
     *
     * @return Version[]|null
     */
    public function getPendingVersions(int $limit = null): ?array;

    /**
     * Get total pending versions count
     */
    public function getPendingVersionsCount(): int;

    /**
     * Find Version entities by a set of criteria
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     */
    public function findBy(array $criteria, array $orderBy = null, ?int $limit = null, ?int $offset = null);

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

    /**
     * Get the id of the most recent version for the resource name and resource ID
     *
     * @param string $resourceName
     * @param int    $resourceId
     *
     * @return int|null
     */
    public function getNewestVersionIdForResource(string $resourceName, int $resourceId): ?int;
}
