<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Repository;

use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Model\ProjectCompleteness;
use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Model\ProjectInterface;

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
     * @return ProjectCompleteness
     */
    public function getProjectCompleteness(ProjectInterface $project, $username = null);

    /**
     * @param ProjectInterface $project
     * @param integer          $status
     * @param string           $username
     *
     * @return array
     */
    public function findProductIdentifiers(ProjectInterface $project, $status, $username);
}
