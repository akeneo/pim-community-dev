<?php

namespace Pim\Bundle\VersioningBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\VersioningBundle\Entity\Version;

/**
 * Version repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionRepository extends EntityRepository
{
    /**
     * @param string $resourceName
     * @param string $resourceId
     *
     * @return Version[]|null
     */
    public function getLogEntries($resourceName, $resourceId)
    {
        return $this->findBy(
            ['resourceId' => $resourceId, 'resourceName' => $resourceName, 'pending' => false],
            ['loggedAt' => 'desc']
        );
    }

    /**
     * @param string $resourceName
     * @param string $resourceId
     *
     * @return Version|null
     */
    public function getOldestLogEntry($resourceName, $resourceId)
    {
        return $this->findOneBy(
            ['resourceId' => $resourceId, 'resourceName' => $resourceName, 'pending' => false],
            ['loggedAt' => 'asc']
        );
    }

    /**
     * @param string $resourceName
     * @param string $resourceId
     *
     * @return Version|null
     */
    public function getNewestLogEntry($resourceName, $resourceId)
    {
        return $this->findOneBy(
            ['resourceId' => $resourceId, 'resourceName' => $resourceName, 'pending' => false],
            ['loggedAt' => 'desc']
        );
    }

    /**
     * Get pending versions
     *
     * @return Version[]
     */
    public function getPendingVersions()
    {
        return $this->findBy(['pending' => true], ['loggedAt' => 'asc']);
    }
}
