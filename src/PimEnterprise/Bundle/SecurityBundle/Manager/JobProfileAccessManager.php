<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Manager;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\UserBundle\Entity\Group;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Model\JobProfileAccessInterface;

/**
 * Job profile access manager
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobProfileAccessManager
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var string */
    protected $objectAccessClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry $registry
     * @param string          $objectAccessClass
     */
    public function __construct(ManagerRegistry $registry, $objectAccessClass)
    {
        $this->registry          = $registry;
        $this->objectAccessClass = $objectAccessClass;
    }

    /**
     * Get user groups that have execute access to a job profile
     *
     * @param JobInstance $jobProfile
     *
     * @return Group[]
     */
    public function getExecuteUserGroups(JobInstance $jobProfile)
    {
        return $this->getRepository()->getGrantedUserGroups($jobProfile, Attributes::EXECUTE);
    }

    /**
     * Get user groups that have edit access to a job profile
     *
     * @param JobInstance $jobProfile
     *
     * @return Group[]
     */
    public function getEditUserGroups(JobInstance $jobProfile)
    {
        return $this->getRepository()->getGrantedUserGroups($jobProfile, Attributes::EDIT);
    }

    /**
     * Grant access on a job profile to specified user groups
     *
     * @param JobInstance $jobProfile
     * @param Group[]     $executeGroups
     * @param Group[]     $editGroups
     */
    public function setAccess(JobInstance $jobProfile, $executeGroups, $editGroups)
    {
        $grantedGroups = [];
        foreach ($editGroups as $group) {
            $this->grantAccess($jobProfile, $group, Attributes::EDIT);
            $grantedGroups[] = $group;
        }

        foreach ($executeGroups as $group) {
            if (!in_array($group, $grantedGroups)) {
                $this->grantAccess($jobProfile, $group, Attributes::EXECUTE);
                $grantedGroups[] = $group;
            }
        }

        if (null !== $jobProfile->getId()) {
            $this->revokeAccess($jobProfile, $grantedGroups);
        }
        $this->getObjectManager()->flush();
    }

    /**
     * Grant specified access on a job profile for the provided user group
     *
     * @param JobInstance $jobProfile
     * @param Group       $group
     * @param string      $accessLevel
     */
    public function grantAccess(JobInstance $jobProfile, Group $group, $accessLevel)
    {
        $access = $this->getJobProfileAccess($jobProfile, $group);
        $access
            ->setExecuteJobProfile(true)
            ->setEditJobProfile($accessLevel === Attributes::EDIT);

        $this->getObjectManager()->persist($access);
    }

    /**
     * Get JobProfileAccess entity for a job profile and user group
     *
     * @param JobInstance $jobProfile
     * @param Group       $group
     *
     * @return JobProfileAccessInterface
     */
    protected function getJobProfileAccess(JobInstance $jobProfile, Group $group)
    {
        $access = $this->getRepository()
            ->findOneby(
                [
                    'jobProfile' => $jobProfile,
                    'userGroup'  => $group
                ]
            );

        if (!$access) {
            /** @var JobProfileAccessInterface $access */
            $access = new $this->objectAccessClass();
            $access
                ->setJobProfile($jobProfile)
                ->setUserGroup($group);
        }

        return $access;
    }

    /**
     * Revoke access to a job profile
     * If $excludedGroups are provided, access will not be revoked for user groups with them
     *
     * @param JobInstance $jobProfile
     * @param Group[]     $excludedGroups
     *
     * @return integer
     */
    protected function revokeAccess(JobInstance $jobProfile, array $excludedGroups = [])
    {
        return $this->getRepository()->revokeAccess($jobProfile, $excludedGroups);
    }

    /**
     * Get repository
     *
     * @return \PimEnterprise\Bundle\SecurityBundle\Entity\Repository\JobProfileAccessRepository
     */
    protected function getRepository()
    {
        return $this->registry->getRepository($this->objectAccessClass);
    }

    /**
     * Get the object manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    protected function getObjectManager()
    {
        return $this->registry->getManagerForClass($this->objectAccessClass);
    }
}
