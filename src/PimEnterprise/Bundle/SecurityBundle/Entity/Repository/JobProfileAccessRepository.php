<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
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
     * Get roles that have the specified access to a job instance
     *
     * @param JobInstance $jobProfile
     * @param string      $accessLevel
     *
     * @return Role[]
     */
    public function getGrantedRoles(JobInstance $jobProfile, $accessLevel)
    {
        $accessField = ($accessLevel === Attributes::EDIT_JOB_PROFILE) ? 'editJobProfile' : 'executeJobProfile';

        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('r')
            ->innerJoin('OroUserBundle:Role', 'r', 'WITH', 'a.role = r.id')
            ->where('a.jobProfile = :jobProfile')
            ->andWhere($qb->expr()->eq(sprintf('a.%s', $accessField), true))
            ->setParameter('jobProfile', $jobProfile);

        return $qb->getQuery()->getResult();
    }

    /**
     * Revoke access to a job profile
     * If excluded roles are provided, access will not be revoked for these roles
     *
     * @param JobInstance $jobProfile
     * @param Role[]      $excludedRoles
     *
     * @return integer
     */
    public function revokeAccess(JobInstance $jobProfile, array $excludedRoles = [])
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->delete()
            ->where('a.jobProfile = :jobProfile')
            ->setParameter('jobProfile', $jobProfile);

        if (!empty($excludedRoles)) {
            $qb
                ->andWhere($qb->expr()->notIn('a.role', ':excludedRoles'))
                ->setParameter('excludedRoles', $excludedRoles);
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
            ->andWhere($qb->expr()->in('ja.role', ':roles'))
            ->setParameter('roles', $user->getRoles())
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
