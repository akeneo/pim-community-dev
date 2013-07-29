<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ImportExportBundle\Form\Type\JobType;
use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\ProductBundle\Controller\Controller;

/**
 * Export controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/export")
 */
class ExportController extends Controller
{
    /**
     * List exports
     * @param Request $request
     *
     * @Route(
     *     "/.{_format}",
     *     name="pim_ie_export_index",
     *     requirements={"_format"="html|json"},
     *     defaults={"_format" = "html"}
     * )
     * @return template
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager ExportDatagridManager */
        $gridManager = $this->get('pim_import_export.datagrid.manager.export');
        $datagridView = $gridManager->getDatagrid()->createView();
        $registry      = $this->getConnectorRegistry();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimImportExportBundle:Export:index.html.twig';
        }

        return $this->render($view, array(
            'datagrid' => $datagridView,
            'connectors' => $registry->getExportJobs(),
        ));
    }

    /**
     * Create export
     * @param Request $request
     *
     * @Route(
     *     "/create",
     *     name="pim_ie_export_create"
     * )
     * @Template("PimImportExportBundle:Export:create.html.twig")
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        $connector     = $request->query->get('connector');
        $alias         = $request->query->get('alias');
        $registry      = $this->getConnectorRegistry();

        $job = new Job($connector, Job::TYPE_EXPORT, $alias);
        $jobDefinition = $registry->getJob($job);

        if (!$jobDefinition) {
            $this->addFlash('error', 'Fail to create an export with an unknown job.');

            return $this->redirect($this->generateUrl('pim_ie_export_index'));
        }

        $form = $this->createForm(new JobType(), $job);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $em->persist($job);
                $em->flush();

                $this->addFlash('success', 'The export has been successfully created.');

                return $this->redirect(
                    $this->generateUrl('pim_ie_export_index')
                );
            }
        }

        return array(
            'form'      => $form->createView(),
            'connector' => $connector,
            'alias'     => $alias,
        );
    }

    /**
     * Show export
     * @param integer $id
     *
     * @Route(
     *     "/{id}",
     *     name="pim_ie_export_show"
     * )
     * @Template("PimImportExportBundle:Export:show.html.twig")
     *
     * @return array
     */
    public function showAction($id)
    {
        $job           = $this->findOr404('PimBatchBundle:Job', $id);
        $registry      = $this->getConnectorRegistry();
        $jobDefinition = $registry->getJob($job);
        if (!$jobDefinition) {
            $this->addFlash('error', sprintf(
                'The following job does not exist anymore. Please check configuration:<br />' .
                'Connector: %s<br />' .
                'Type: %s<br />' .
                'Alias: %s',
                $job->getConnector(), $job->getType(), $job->getAlias()
            ));

            return $this->redirect($this->generateUrl('pim_ie_export_index'));
        }
        $job->setJobDefinition($jobDefinition);
        $validator = $this->getValidator();

        return array(
            'job'        => $job,
            'violations' => $validator->validate($job),
        );
    }

    protected function getConnectorRegistry()
    {
        return $this->get('pim_batch.connectors');
    }
}
