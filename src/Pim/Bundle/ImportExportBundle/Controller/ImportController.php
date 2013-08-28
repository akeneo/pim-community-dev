<?php

namespace Pim\Bundle\ImportExportBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pim\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Import controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImportController extends JobInstanceController
{
    /**
     * Upload a file to run the import
     *
     * @param Request $request
     * @param integer $id
     *
     * @return template
     */
    public function uploadAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToIndexView();
        }

        try {
            $jobInstance = $this->getJobInstance($id);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToIndexView();
        }

        return $this->render(
            'PimImportExportBundle:Import:upload.html.twig',
            array(
                'form'        => $this->createUploadForm()->createView(),
                'jobInstance' => $jobInstance,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getJobType()
    {
        return JobInstance::TYPE_IMPORT;
    }
}
