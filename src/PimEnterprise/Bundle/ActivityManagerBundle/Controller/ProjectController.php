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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Project controller.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectController extends Controller
{
    /**
     * @param string $label
     *
     * @return Response
     */
    public function showAction($label)
    {
        return $this->render('ActivityManagerBundle:Project:show.html.twig', ['label' => $label]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $projectData = $request->request->get('project');

        $project = $this->container->get('activity_manager.project.factory')
            ->create();

        $this->container->get('activity_manager.project.updater')
            ->update($project, $projectData);

        $violations = $this->container->get('validator')
            ->validate($project);


        if (0 === $violations->count()) {
            $this->container->get('activity_manager.saver.project')
                ->save($project);

            $this->test($request, $project->getId());

            $normalizedProject = $this->container->get('activity_manager.project.normalizer')
                ->normalize($project, 'internal_api');

            return new JsonResponse($normalizedProject, 201);
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return new JsonResponse($errors, 400);
    }

    private function test(Request $request, $id)
    {
        $simpleJobLauncher = $this->container->get('akeneo_batch.launcher.simple_job_launcher');
        $jobInstanceRepo = $this->container->get('pim_import_export.repository.job_instance');
//        $massActionDispatcher = $this->container->get('pim_datagrid.extension.mass_action.dispatcher');


//        $params = $massActionDispatcher->getRawFilters($request);
        $params['values'] = [];
        $jobInstance = $jobInstanceRepo->findOneByIdentifier('test_query'); //TODO: to change !!!!!!
        $configuration = ['filters' => $params['values'], 'project_id' => $id];

        $simpleJobLauncher->launch($jobInstance, $this->getUser(), $configuration);
    }
}
