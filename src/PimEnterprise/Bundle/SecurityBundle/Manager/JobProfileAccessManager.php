<?php

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\UserBundle\Entity\Role;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use PimEnterprise\Bundle\SecurityBundle\Model\JobProfileAccessInterface;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

/**
 * Job profile access manager
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobProfileAccessManager
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $jobProfileAccessClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param string          $jobProfileAccessClass
     */
    public function __construct(ManagerRegistry $registry, $jobProfileAccessClass)
    {
        $this->registry              = $registry;
        $this->jobProfileAccessClass = $jobProfileAccessClass;
    }

    /**
     * Get roles that have execute access to a job profile
     *
     * @param JobInstance $jobProfile
     *
     * @return Role[]
     */
    public function getExecuteRoles(JobInstance $jobProfile)
    {
        return $this->getRepository()->getGrantedRoles($jobProfile, JobProfileVoter::EXECUTE_JOB_PROFILE);
    }

    /**
     * Get roles that have edit access to a job profile
     *
     * @param JobInstance $jobProfile
     *
     * @return Role[]
     */
    public function getEditRoles(JobInstance $jobProfile)
    {
        return $this->getRepository()->getGrantedRoles($jobProfile, JobProfileVoter::EDIT_JOB_PROFILE);
    }

    /**
     * Grant access on a job profile to specified roles
     *
     * @param JobInstance $jobProfile
     * @param Roles[]     $executeRoles
     * @param Roles[]     $editRoles
     */
    public function setAccess(JobInstance $jobProfile, $executeRoles, $editRoles)
    {
        $grantedRoles = [];
        foreach ($editRoles as $role) {
            $this->grantAccess($jobProfile, $role, JobProfileVoter::EDIT_JOB_PROFILE);
            $grantedRoles[] = $role;
        }

        foreach ($executeRoles as $role) {
            if (!in_array($role, $grantedRoles)) {
                $this->grantAccess($jobProfile, $role, JobProfileVoter::EXECUTE_JOB_PROFILE);
                $grantedRoles[] = $role;
            }
        }

        $this->revokeAccess($jobProfile, $grantedRoles);
        $this->getObjectManager()->flush();
    }

    /**
     * Grant specified access on a job profile for the provided role
     *
     * @param JobInstance $jobProfile
     * @param Role        $role
     * @param string      $accessLevel
     */
    public function grantAccess(JobInstance $jobProfile, Role $role, $accessLevel)
    {
        $access = $this->getJobProfileAccess($jobProfile, $role);
        $access
            ->setExecuteJobProfile(true)
            ->setEditJobProfile($accessLevel === JobProfileVoter::EDIT_JOB_PROFILE);

        $this->getObjectManager()->persist($access);
    }

    /**
     * Get JobProfileAccess entity for a job profile and role
     *
     * @param JobInstance $jobProfile
     * @param Role        $role
     *
     * @return JobProfileAccessInterface
     */
    protected function getJobProfileAccess(JobInstance $jobProfile, Role $role)
    {
        $access = $this->getRepository()
            ->findOneby(
                [
                    'jobProfile' => $jobProfile,
                    'role'       => $role
                ]
            );

        if (!$access) {
            $access = new $this->jobProfileAccessClass();
            $access
                ->setJobProfile($jobProfile)
                ->setRole($role);
        }

        return $access;
    }

    /**
     * Revoke access to a job profile
     * If $excludedRoles are provided, access will not be revoked for roles with them
     *
     * @param JobInstance $jobProfile
     * @param Role[]      $excludedRoles
     *
     * @return integer
     */
    protected function revokeAccess(JobInstance $jobProfile, array $excludedRoles = [])
    {
        return $this->getRepository()->revokeAccess($jobProfile, $excludedRoles);
    }

    /**
     * Get repository
     *
     * @return JobProfileAccessRepository
     */
    protected function getRepository()
    {
        return $this->registry->getRepository($this->jobProfileAccessClass);
    }

    /**
     * Get the object manager
     *
     * @return 0bjectManager
     */
    protected function getObjectManager()
    {
        return $this->registry->getManagerForClass($this->jobProfileAccessClass);
    }
}
