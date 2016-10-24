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

use PimEnterprise\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter;
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

        $this->addParametersToRequest($request, $filters);

        $project = $this->container->get('activity_manager.factory.project')
            ->create();

        $this->container->get('activity_manager.updater.project')
            ->update($project, $projectData);

        $violations = $this->container->get('validator')
            ->validate($project);

        if (0 === $violations->count()) {
            $this->container->get('activity_manager.saver.project')
                ->save($project);

            $this->executeJob($request, $project->getId());

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

    /**
     * @param Request $request
     * @param string  $id
     */
    private function executeJob(Request $request, $id)
    {
        $simpleJobLauncher = $this->container->get('akeneo_batch.launcher.simple_job_launcher');
        $jobInstanceRepo = $this->container->get('pim_import_export.repository.job_instance');
        $filterAdapter = $this->container->get('pim_datagrid.adapter.oro_to_pim_grid_filter');

        $filters = $filterAdapter->adapt($request);

        $jobInstance = $jobInstanceRepo->findOneByIdentifier('project_calculation');
        $configuration = ['filters' => $filters, 'project_id' => $id];

        $simpleJobLauncher->launch($jobInstance, $this->getUser(), $configuration);
    }

    /**
     * It adds values to the request as it's needed by the adapter to transform oro grid filters into PQB filter.
     *
     * @param Request $request
     * @param string  $filters
     */
    private function addParametersToRequest(Request $request, $filters)
    {
        $request->query->add(
            [
                'gridName'   => OroToPimGridFilterAdapter::PRODUCT_GRID_NAME,
                'actionName' => 'mass_edit', //Fake mass action, needed for the grid filter adapter.
                'inset'      => false,
                'filters'    => $filters,
            ]
        );
    }
}
