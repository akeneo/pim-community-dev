<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Repository;

use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
interface ProjectCompletenessRepositoryInterface
{
    /**
     * Get the project completeness for a contributor.
     *
     * @param ProjectInterface $project
     * @param string|null      $username
     *
     * @return array
     */
    public function getProjectCompleteness(ProjectInterface $project, $username = null);
}
