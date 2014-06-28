<?php

namespace Pim\Bundle\VersioningBundle\Doctrine\ORM;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

/**
 * Version repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionRepository extends EntityRepository implements VersionRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLogEntries($resourceName, $resourceId)
    {
        return $this->findBy(
            ['resourceId' => $resourceId, 'resourceName' => $resourceName, 'pending' => false],
            ['loggedAt' => 'desc']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOldestLogEntry($resourceName, $resourceId)
    {
        return $this->findOneBy(
            ['resourceId' => $resourceId, 'resourceName' => $resourceName, 'pending' => false],
            ['loggedAt' => 'asc']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getNewestLogEntry($resourceName, $resourceId)
    {
        return $this->findOneBy(
            ['resourceId' => $resourceId, 'resourceName' => $resourceName, 'pending' => false],
            ['loggedAt' => 'desc']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingVersions()
    {
        return $this->findBy(['pending' => true], ['loggedAt' => 'asc']);
    }
}
