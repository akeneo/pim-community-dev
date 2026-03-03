<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Doctrine\ORM;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\UuidInterface;

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
    public function getLogEntries(string $resourceName, ?string $resourceId, ?UuidInterface $resourceUuid, ?int $limit = null)
    {
        $params = [
            'resourceName' => $resourceName,
            'pending' => false,
        ];
        if (null !== $resourceUuid) {
            $params['resourceUuid'] = $resourceUuid;
        } else {
            $params['resourceId'] = $resourceId;
        }

        return $this->findBy($params, ['version' => 'desc'], $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function getOldestLogEntry($resourceName, $resourceId, ?UuidInterface $resourceUuid, $pending = false)
    {
        return $this->getOneLogEntry($resourceName, $resourceId, $resourceUuid, $pending, 'asc');
    }

    /**
     * {@inheritdoc}
     */
    public function getNewestLogEntry($resourceName, $resourceId, ?UuidInterface $resourceUuid, $pending = false)
    {
        return $this->getOneLogEntry($resourceName, $resourceId, $resourceUuid, $pending, 'desc');
    }

    /**
     * {@inheritdoc}
     */
    public function getNewestLogEntryForRessources($resourceNames)
    {
        return $this->findOneBy(['resourceName' => $resourceNames], ['loggedAt' => 'desc']);
    }

    /**
     * {@inheritdoc}
     */
    public function getPendingVersions($limit = null)
    {
        return $this->findBy(['pending' => true], ['version' => 'asc'], $limit);
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
     * @return QueryBuilder
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
                UserInterface::class,
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
        $connection = $this->_em->getConnection();
        $qb = $connection->createQueryBuilder();
        $qb->select('id', 'resource_id', 'resource_name', 'version')->from('pim_versioning_version');

        if (isset($options['resource_name'])) {
            $qb->andWhere('resource_name = :resource_name')
               ->setParameter(':resource_name', $options['resource_name'], \PDO::PARAM_STR);
        }

        if (isset($options['date_operator']) && isset($options['limit_date'])) {
            if ('<' === $options['date_operator']) {
                $qb->andWhere('logged_at < :logged_at');
            } else {
                $qb->andWhere('logged_at > :logged_at');
            }
            $qb->setParameter(':logged_at', $options['limit_date']->format('Y-m-d'), \PDO::PARAM_STR);
        }

        $cursorOptions = [];
        if (isset($options['batch_size'])) {
            $cursorOptions['page_size'] = $options['batch_size'];
        }


        return $qb->execute();
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
     * @param string|null    $resourceId
     * @param bool|null $pending
     * @param string    $sort
     *
     * @return Version|null
     */
    protected function getOneLogEntry($resourceName, $resourceId, ?UuidInterface $resourceUuid, $pending, $sort)
    {
        $criteria = ['resourceName' => $resourceName];
        if (null !== $resourceUuid) {
            $criteria['resourceUuid'] = $resourceUuid;
        } else {
            $criteria['resourceId'] = $resourceId;
        }

        if (null !== $pending) {
            $criteria['pending'] = $pending;
        }

        return $this->findOneBy($criteria, ['version' => $sort]);
    }
}
