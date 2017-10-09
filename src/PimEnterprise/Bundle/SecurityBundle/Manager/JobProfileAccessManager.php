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
use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Component\User\Model\GroupInterface;
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

    /** @var BulkObjectDetacherInterface */
    protected $objectDetacher;

    /** @var string */
    protected $objectAccessClass;

    /**
     * @param JobProfileAccessRepository  $repository
     * @param BulkSaverInterface          $saver
     * @param BulkObjectDetacherInterface $objectDetacher
     * @param string                      $objectAccessClass
     */
    public function __construct(
        JobProfileAccessRepository $repository,
        BulkSaverInterface $saver,
        BulkObjectDetacherInterface $objectDetacher,
        $objectAccessClass
    ) {
        $this->repository = $repository;
        $this->saver = $saver;
        $this->objectDetacher = $objectDetacher;
        $this->objectAccessClass = $objectAccessClass;
    }

    /**
     * Get user groups that have execute access to a job profile
     *
     * @param JobInstance $jobProfile
     *
     * @return GroupInterface[]
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
     * @return GroupInterface[]
     */
    public function getEditUserGroups(JobInstance $jobProfile)
    {
        return $this->repository->getGrantedUserGroups($jobProfile, Attributes::EDIT);
    }

    /**
     * Grant access on a job profile to specified user groups
     *
     * @param JobInstance      $jobProfile
     * @param GroupInterface[] $executeGroups
     * @param GroupInterface[] $editGroups
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
        $this->objectDetacher->detachAll($grantedAccesses);
    }

    /**
     * Grant specified access on a job profile for the provided user group
     *
     * @param JobInstance    $jobProfile
     * @param GroupInterface $group
     * @param string         $accessLevel
     */
    public function grantAccess(JobInstance $jobProfile, GroupInterface $group, $accessLevel)
    {
        $access = $this->buildGrantAccess($jobProfile, $group, $accessLevel);
        $this->saver->saveAll([$access]);
        $this->objectDetacher->detachAll([$access]);
    }

    /**
     * Revoke access to a job profile
     * If $excludedGroups are provided, access will not be revoked for user groups with them
     *
     * @param JobInstance      $jobProfile
     * @param GroupInterface[] $excludedGroups
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
     * @param JobInstance    $jobProfile
     * @param GroupInterface $group
     *
     * @return JobProfileAccessInterface
     */
    protected function getJobProfileAccess(JobInstance $jobProfile, GroupInterface $group)
    {
        $access = $this->repository
            ->findOneBy(
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
     * @param JobInstance    $jobProfile
     * @param GroupInterface $group
     * @param string         $accessLevel
     *
     * @return JobProfileAccessInterface
     */
    protected function buildGrantAccess(JobInstance $jobProfile, GroupInterface $group, $accessLevel)
    {
        $access = $this->getJobProfileAccess($jobProfile, $group);
        $access
            ->setExecuteJobProfile(true)
            ->setEditJobProfile($accessLevel === Attributes::EDIT);

        return $access;
    }
}
