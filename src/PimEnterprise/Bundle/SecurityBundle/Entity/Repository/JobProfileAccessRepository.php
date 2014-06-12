<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

/**
 * Job profile access repository
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobProfileAccessRepository extends EntityRepository
{
    /**
     * @var TableNameBuilder
     */
    protected $tableNameBuilder;

    /**
     * Get roles that have the specified access to a category
     *
     * @param CategoryInterface $category
     * @param string            $accessLevel
     *
     * @return Role[]
     */
    public function getGrantedRoles(JobInstance $jobProfile, $accessLevel)
    {
        $accessField = ($accessLevel === JobProfileVoter::EDIT_JOB_PROFILE) ? 'editJobProfile' : 'executeJobProfile';

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
}
