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
        $datagridViewFilters = [];
        $projectData = $request->request->get('project');

        parse_str($projectData['datagrid_view']['filters'], $datagridViewFilters);

        $filters = json_encode($datagridViewFilters['f']);
        $filters = $this->container->get('activity_manager.converter.filter')
            ->convert($request, $filters);

        $channelCode = $datagridViewFilters['f']['scope']['value'];
        $channel = $this->container->get('pim_catalog.repository.channel')
            ->findOneByIdentifier($channelCode);

        $locale = $this->container->get('pim_catalog.repository.locale')
            ->findOneByIdentifier($projectData['locale']);

        $projectData['product_filters'] = $filters;
        $projectData['owner'] = $this->getUser();
        $projectData['channel'] = $channel;
        $projectData['locale'] = $locale;

        $datagridViewData = [];
        if (isset($projectData['datagrid_view'])) {
            $datagridViewData = $projectData['datagrid_view'];
            $datagridViewData['type'] = DatagridViewTypes::PROJECT_VIEW;
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
