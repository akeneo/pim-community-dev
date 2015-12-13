<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Akeneo\Component\Batch\Model\JobInstance;

/**
 * Job profile access interface
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
interface JobProfileAccessInterface extends AccessInterface
{
    /**
     * Set a job profile
     *
     * @param JobInstance $jobProfile
     *
     * @return JobProfileAccessInterface
     */
    public function setJobProfile(JobInstance $jobProfile);

    /**
     * Get a job profile
     *
     * @return JobInstance
     */
    public function getJobProfile();

    /**
     * Predicate to define if the job profile is executable
     *
     * @param bool $executeJobProfile
     *
     * @return JobProfileAccessInterface
     */
    public function setExecuteJobProfile($executeJobProfile);

    /**
     * Predicate to know if job profile is executable
     *
     * @return bool
     */
    public function isExecuteJobProfile();

    /**
     * Predicate to define if the job profile is ediable
     *
     * @param bool $editJobProfile
     *
     * @return JobProfileAccessInterface
     */
    public function setEditJobProfile($editJobProfile);

    /**
     * Predicate to know if job profile is editable
     *
     * @return bool
     */
    public function isEditJobProfile();
}
