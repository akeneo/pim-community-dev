<?php

namespace Pim\Bundle\VersioningBundle\Doctrine\ORM;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;

/**
 * Version repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionRepository extends EntityRepository implements VersionRepositoryInterface, CursorableRepositoryInterface
{
    /** @var CursorFactoryInterface */
    protected $cursorFactory;

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
    public function getNewestLogEntryForRessources($resourceNames)
    {
        return $this->findOneBy(['resourceName' => $resourceNames], ['loggedAt' => 'desc'], 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingVersions($limit = null)
    {
        return $this->findBy(['pending' => true], ['loggedAt' => 'asc'], $limit);
    }

    /**
     * Get total pending versions count
     *
     * @return int
     */
    public function getPendingVersionsCount()
    {
        $qb = $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.pending = true');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array $parameters
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createDatagridQueryBuilder(array $parameters = [])
    {
        $userNameExpr = "CONCAT(CONCAT(CONCAT(u.firstName, ' '), CONCAT(u.lastName, ' - ')), u.email)";
        $removedUserNameExpr = "CONCAT(v.author, ' - Removed user')";
        $userExpr = sprintf('CASE WHEN u.id IS NOT NULL THEN %s ELSE %s END', $userNameExpr, $removedUserNameExpr);
        $contextExpr = "CASE WHEN v.context IS NOT NULL THEN CONCAT(CONCAT(' (', v.context), ')') ELSE '' END";

        $authorExpr = sprintf('CONCAT(%s, %s)', $userExpr, $contextExpr);

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('v.id, v.changeset as changeset, v.loggedAt as loggedAt, v.version as version')
            ->addSelect('v.resourceId as resourceId')
            ->from($this->_entityName, 'v', 'v.id');

        $qb
            ->addSelect(sprintf('%s as author', $authorExpr))
            ->leftJoin(
                'PimUserBundle:User',
                'u',
                'WITH',
                'u.username = v.author'
            )
            ->where('v.pending = false')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->eq('v.resourceName', ':objectClass'),
                    $qb->expr()->eq('v.resourceId', ':objectId')
                )
            );

        if (!empty($parameters['objectClass'])) {
            $qb->setParameter(':objectClass', $parameters['objectClass']);
        }

        if (!empty($parameters['objectId'])) {
            $qb->setParameter(':objectId', $parameters['objectId']);
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findPotentiallyPurgeableBy(array $options = [])
    {
        if (null === $this->cursorFactory) {
            throw new \RuntimeException('The cursor factory is not initialized');
        }

        $qb = $this->createQueryBuilder('v');

        if (isset($options['resource_name'])) {
            $qb->where('v.resourceName = :resourceName');
            $qb->setParameter(':resourceName', $options['resource_name']);
        }

        if (isset($options['date_operator']) && isset($options['limit_date'])) {
            if ('<' === $options['date_operator']) {
                $qb->andWhere($qb->expr()->lt('v.loggedAt', ':limit_date'));
            } else {
                $qb->andWhere($qb->expr()->gt('v.loggedAt', ':limit_date'));
            }
            $qb->setParameter('limit_date', $options['limit_date'], Type::DATETIME);
        }

        return $this->cursorFactory->createCursor($qb);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIds(array $versionIds)
    {
        if (empty($versionIds)) {
            throw new \InvalidArgumentException('Array must contain at least one version id');
        }

        $qb = $this->createQueryBuilder('v');
        $qb->where($qb->expr()->in('v.id', ':version_ids'));
        $qb->setParameter(':version_ids', $versionIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getNewestVersionIdForResource($resourceName, $resourceId)
    {
        $qb = $this->createQueryBuilder('v');
        $qb->select('v.id')
            ->where($qb->expr()->eq('v.resourceName', ':resource_name'))
            ->andWhere(
                $qb->expr()->eq('v.resourceId', ':resource_id')
            )
            ->orderBy('v.version', 'desc')
            ->setMaxResults(1);

        $qb->setParameter(':resource_name', $resourceName)
            ->setParameter(':resource_id', $resourceId);

        try {
            $versionId = (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            $versionId = null;
        }

        return $versionId;
    }

    /**
     * @param CursorFactoryInterface $cursorFactory
     */
    public function setCursorFactory(CursorFactoryInterface $cursorFactory)
    {
        $this->cursorFactory = $cursorFactory;
    }

    /**
     * Get one log entry
     *
     * @param string    $resourceName
     * @param string    $resourceId
     * @param bool|null $pending
     * @param string    $sort
     *
     * @return \Akeneo\Component\Versioning\Model\Version|null
     */
    protected function getOneLogEntry($resourceName, $resourceId, $pending, $sort)
    {
        $criteria = ['resourceId' => $resourceId, 'resourceName' => $resourceName];
        if (null !== $pending) {
            $criteria['pending'] = $pending;
        }

        return $this->findOneBy(
            $criteria,
            ['loggedAt' => $sort]
        );
    }
}
