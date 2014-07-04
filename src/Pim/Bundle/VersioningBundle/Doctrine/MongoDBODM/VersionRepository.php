<?php

namespace Pim\Bundle\VersioningBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

/**
 * MongoDB version repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionRepository extends DocumentRepository implements VersionRepositoryInterface
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
    public function getOldestLogEntry($resourceName, $resourceId, $pending = false)
    {
        return $this->getOneLogEntry($resourceName, $resourceId, $pending, 'asc');
    }

    /**
     * {@inheritdoc}
     */
    public function getNewestLogEntry($resourceName, $resourceId, $pending = false)
    {
        return $this->getOneLogEntry($resourceName, $resourceId, $pending, 'desc');
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingVersions()
    {
        return $this->findBy(['pending' => true], ['loggedAt' => 'asc']);
    }

    /**
     * @param array $params
     *
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder(array $params = [])
    {
        $qb = $this->createQueryBuilder();

        if (!empty($params['objectClass']) && !empty($params['objectId'])) {
            $qb->field('resourceName')->equals($params['objectClass']);
            $qb->field('resourceId')->equals($params['objectId']);
        }

        return $qb;
    }

    /**
     * Get one log entry
     *
     * @param string    $resourceName
     * @param string    $resourceId
     * @param bool|null $pending
     * @param string    $sort
     *
     * @return Version|null
     */
    protected function getOneLogEntry($resourceName, $resourceId, $pending, $sort)
    {
        $criteria = ['resourceId' => $resourceId, 'resourceName' => $resourceName];
        if (null !== $pending) {
            $criteria['pending'] = $pending;
        }

        return current(
            $this->findBy(
                $criteria,
                ['loggedAt' => $sort],
                1
            )
        );
    }
}
