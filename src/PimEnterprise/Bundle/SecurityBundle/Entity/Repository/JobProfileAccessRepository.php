<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\User;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Pim\Bundle\CatalogBundle\Doctrine\TableNameBuilder;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Job profile access repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobProfileAccessRepository extends EntityRepository implements AccessRepositoryInterface
{
    /**
     * @var TableNameBuilder
     */
    protected $tableNameBuilder;

    /**
     * Get user groups that have the specified access to a job instance
     *
     * @param JobInstance $jobProfile
     * @param string      $accessLevel
     *
     * @return Group[]
     */
    public function getGrantedUserGroups(JobInstance $jobProfile, $accessLevel)
    {
        $qb = $this->createQueryBuilder('ja');
        $qb
            ->select('g')
            ->innerJoin('OroUserBundle:Group', 'g', 'WITH', 'ja.userGroup = g.id')
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
     * @return integer
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
     * @param User   $user
     * @param string $accessLevel
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getGrantedJobsQB(User $user, $accessLevel)
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
    public function getGrantedEntitiesQB(User $user, $accessLevel)
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
        return ($accessLevel === Attributes::EDIT_JOB_PROFILE)
            ? 'ja.editJobProfile'
            : 'ja.executeJobProfile';
    }
}
