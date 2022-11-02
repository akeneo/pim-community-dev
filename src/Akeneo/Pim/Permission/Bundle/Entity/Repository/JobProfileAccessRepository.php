<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Entity\Repository;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\Repository\AccessRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Job profile access repository
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobProfileAccessRepository extends EntityRepository implements
    AccessRepositoryInterface,
    IdentifiableObjectRepositoryInterface
{
    /**
     * Get user groups that have the specified access to a job instance
     *
     * @param JobInstance $jobProfile
     * @param string      $accessLevel
     *
     * @return GroupInterface[]
     */
    public function getGrantedUserGroups(JobInstance $jobProfile, $accessLevel)
    {
        $qb = $this->createQueryBuilder('ja');
        $qb
            ->select('g')
            ->innerJoin(Group::class, 'g', 'WITH', 'ja.userGroup = g.id')
            ->where('ja.jobProfile = :jobProfile')
            ->andWhere($qb->expr()->eq($this->getAccessField($accessLevel), true))
            ->setParameter('jobProfile', $jobProfile);

        return $qb->getQuery()->getResult();
    }

    /**
     * Revoke access to a job profile
     * If excluded user groups are provided, access will not be revoked for these groups
     *
     * @param JobInstance $jobProfile
     * @param Group[]     $excludedGroups
     *
     * @return int
     */
    public function revokeAccess(JobInstance $jobProfile, array $excludedGroups = [])
    {
        $qb = $this->createQueryBuilder('ja');
        $qb
            ->delete()
            ->where('ja.jobProfile = :jobProfile')
            ->setParameter('jobProfile', $jobProfile);

        if (!empty($excludedGroups)) {
            $qb
                ->andWhere($qb->expr()->notIn('ja.userGroup', ':excludedGroups'))
                ->setParameter('excludedGroups', $excludedGroups);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Get granted job profiles query builder
     *
     * @param UserInterface $user
     * @param string        $accessLevel
     *
     * @return QueryBuilder
     */
    public function getGrantedJobsQB(UserInterface $user, $accessLevel)
    {
        $qb = $this->createQueryBuilder('ja');
        $qb
            ->andWhere($qb->expr()->in('ja.userGroup', ':groups'))
            ->setParameter('groups', $user->getGroups()->toArray())
            ->andWhere($qb->expr()->eq($this->getAccessField($accessLevel), true))
            ->innerJoin('ja.jobProfile', 'jp', 'jp.id')
            ->select('jp.id');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getGrantedEntitiesQB(UserInterface $user, $accessLevel)
    {
        return $this->getGrantedJobsQB($user, $accessLevel);
    }

    /**
     * Get the access field depending of access level sent
     *
     * @param string $accessLevel
     *
     * @return string
     */
    protected function getAccessField($accessLevel)
    {
        return ($accessLevel === Attributes::EDIT)
            ? 'ja.editJobProfile'
            : 'ja.executeJobProfile';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['job_profile', 'user_group'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        list($jobProfileCode, $userGroupName) = explode('.', $identifier, 2);

        $associationMappings = $this->_em->getClassMetadata($this->_entityName)->getAssociationMappings();
        $jobProfileClass = $associationMappings['jobProfile']['targetEntity'];

        $qb = $this->createQueryBuilder('a')
            ->innerJoin($jobProfileClass, 'j', 'WITH', 'a.jobProfile = j.id')
            ->innerJoin(Group::class, 'g', 'WITH', 'a.userGroup = g.id')
            ->where('j.code = :jobProfileCode')
            ->andWhere('g.name = :userGroupName')
            ->setParameter('jobProfileCode', $jobProfileCode)
            ->setParameter('userGroupName', $userGroupName);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
