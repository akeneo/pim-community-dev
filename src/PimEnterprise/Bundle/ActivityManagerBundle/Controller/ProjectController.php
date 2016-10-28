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
        $projectData['owner'] = $this->getUser();
        parse_str($projectData['datagrid_view']['filters'], $output);

        $filters = json_encode($output['f']);

        $filters = $this->container->get('activity_manager.converter.filter')
            ->convert($request, $filters);
        $projectData['product_filters'] = $filters;

        $project = $this->container->get('activity_manager.factory.project')
            ->create();

        $this->container->get('activity_manager.updater.project')
            ->update($project, $projectData);

        $violations = $this->container->get('validator')
            ->validate($project);

        if (0 === $violations->count()) {
            $this->container->get('activity_manager.saver.project')
                ->save($project);

            $this->container->get('activity_manager.launcher.job.project_calculation')
                ->launch($this->getUser(), $project);

            $normalizedProject = $this->container->get('activity_manager.normalizer.project')
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
}
