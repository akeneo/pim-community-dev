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

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\JobProfileAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\Model\JobProfileAccessInterface;

/**
 * Job profile access manager
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobProfileAccessManager
{
    /** @var JobProfileAccessRepository */
    protected $repository;

    /** @var BulkSaverInterface */
    protected $saver;

    /** @var string */
    protected $objectAccessClass;

    /**
     * @param JobProfileAccessRepository $repository
     * @param BulkSaverInterface         $saver
     * @param string                     $objectAccessClass
     */
    public function __construct(JobProfileAccessRepository $repository, BulkSaverInterface $saver, $objectAccessClass)
    {
        $this->repository = $repository;
        $this->saver = $saver;
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
        return $this->repository->getGrantedUserGroups($jobProfile, Attributes::EXECUTE);
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
        return $this->repository->getGrantedUserGroups($jobProfile, Attributes::EDIT);
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
        $grantedAccesses = [];
        foreach ($editGroups as $group) {
            $grantedAccesses[] = $this->buildGrantAccess($jobProfile, $group, Attributes::EDIT);
            $grantedGroups[] = $group;
        }

        foreach ($executeGroups as $group) {
            if (!in_array($group, $grantedGroups)) {
                $grantedAccesses[] = $this->buildGrantAccess($jobProfile, $group, Attributes::EXECUTE);
                $grantedGroups[] = $group;
            }
        }

        if (null !== $jobProfile->getId()) {
            $this->revokeAccess($jobProfile, $grantedGroups);
        }
        $this->saver->saveAll($grantedAccesses);
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
        $access = $this->buildGrantAccess($jobProfile, $group, $accessLevel);
        $this->saver->saveAll([$access]);
    }

    /**
     * Revoke access to a job profile
     * If $excludedGroups are provided, access will not be revoked for user groups with them
     *
     * @param JobInstance $jobProfile
     * @param Group[]     $excludedGroups
     *
     * @return int
     */
    public function revokeAccess(JobInstance $jobProfile, array $excludedGroups = [])
    {
        return $this->repository->revokeAccess($jobProfile, $excludedGroups);
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
        $access = $this->repository
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
     * Build specified access on a job profile for the provided user group
     *
     * @param JobInstance $jobProfile
     * @param Group       $group
     * @param string      $accessLevel
     *
     * @return JobProfileAccessInterface
     */
    protected function buildGrantAccess(JobInstance $jobProfile, Group $group, $accessLevel)
    {
        $access = $this->getJobProfileAccess($jobProfile, $group);
        $access
            ->setExecuteJobProfile(true)
            ->setEditJobProfile($accessLevel === Attributes::EDIT);

        return $access;
    }
}
