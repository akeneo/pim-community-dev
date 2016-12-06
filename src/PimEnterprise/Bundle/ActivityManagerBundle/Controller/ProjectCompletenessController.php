<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Controller;

use Akeneo\ActivityManager\Component\Model\Project;
use Akeneo\ActivityManager\Component\Voter\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCompletenessController extends Controller
{
    /**
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function showAction(Project $project, Request $request)
    {
        $this->denyAccessUnlessGranted([ProjectVoter::OWN, ProjectVoter::CONTRIBUTE], $project);

        $contributor = $this->isGranted(ProjectVoter::OWN, $project) ? $request->get('contributor') : null;

        $projectCompleteness = $this->get('activity_manager.repository.native_sql.project_completeness')
            ->getProjectCompleteness($project, $contributor);

        return new JsonResponse($projectCompleteness);
    }
}
