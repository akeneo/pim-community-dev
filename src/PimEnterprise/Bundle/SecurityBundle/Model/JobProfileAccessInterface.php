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

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;

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
     * @param boolean $executeJobProfile
     *
     * @return JobProfileAccessInterface
     */
    public function setExecuteJobProfile($executeJobProfile);

    /**
     * Predicate to know if job profile is executable
     *
     * @return boolean
     */
    public function isExecuteJobProfile();

    /**
     * Predicate to define if the job profile is ediable
     *
     * @param boolean $editJobProfile
     *
     * @return JobProfileAccessInterface
     */
    public function setEditJobProfile($editJobProfile);

    /**
     * Predicate to know if job profile is editable
     *
     * @return boolean
     */
    public function isEditJobProfile();
}
