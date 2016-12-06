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

use Akeneo\ActivityManager\Component\Model\DatagridViewTypes;
use Akeneo\ActivityManager\Component\Model\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Project controller.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $datagridViewFilters = [];
        $projectData = $request->request->get('project');

        parse_str($projectData['datagrid_view']['filters'], $datagridViewFilters);

        $filters = json_encode($datagridViewFilters['f']);
        $filters = $this->container->get('activity_manager.converter.filter')
            ->convert($request, $filters);

        $projectData['product_filters'] = $filters;
        $projectData['owner'] = $this->getUser();
        $projectData['channel'] = $datagridViewFilters['f']['scope']['value'];

        $datagridViewData = [];
        if (isset($projectData['datagrid_view'])) {
            $datagridViewData = $projectData['datagrid_view'];
            $datagridViewData['type'] = DatagridViewTypes::PROJECT_VIEW;
            $datagridViewData['owner'] = $projectData['owner'];
            $datagridViewData['label'] = sprintf('Project %s', time());
            $datagridViewData['datagrid_alias'] = 'product-grid';
        }

        $datagridView = $this->container->get('pim_datagrid.factory.datagrid_view')
            ->create();

        $this->container->get('pim_datagrid.updater.datagrid_view')
            ->update($datagridView, $datagridViewData);

        $projectData['datagrid_view'] = $datagridView;

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

            $normalizedProject = $this->container->get('pim_internal_api_serializer')
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

    /**
     * Returns Projects in terms of search and options.
     * Options accept 'limit' => (int) and 'page' => (int) and 'user' => UserInterface.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
    {
        $projectRepository = $this->container->get('activity_manager.repository.doctrine.project');
        $serializer = $this->container->get('pim_internal_api_serializer');
        $options = $request->query->get('options', ['limit' => 20, 'page' => 1]);

        $projects = $projectRepository->findBySearch(
            $request->query->get('search'),
            [
                'limit' => $options['limit'],
                'page' => $options['page'],
                'user' => $this->getUser(),
            ]
        );

        $normalizedProjects = $serializer->normalize($projects, 'internal_api');

        return new JsonResponse($normalizedProjects, 200);
    }

    /**
     * Returns users that belong to the project.
     *
     * @param Request $request
     * @param string  $projectCode
     *
     * @return JsonResponse
     */
    public function searchContributorsAction($projectCode, Request $request)
    {
        $projectRepository = $this->container->get('activity_manager.repository.project');
        $userRepository = $this->container->get('activity_manager.repository.user');
        $serializer = $this->container->get('pim_internal_api_serializer');

        $project = $projectRepository->findOneByIdentifier($projectCode);

        if (null === $project) {
            return new JsonResponse(null, 404);
        }

        $options = $request->query->get('options', ['limit' => 20, 'page' => 1]);

        $users = $userRepository->findBySearch(
            $request->query->get('search'),
            [
                'limit' => $options['limit'],
                'page' => $options['page'],
                'project' => $project,
            ]
        );

        $normalizedProjects = $serializer->normalize($users, 'internal_api');

        return new JsonResponse($normalizedProjects, 200);
    }
}
