<?php

namespace Oro\Bundle\ImportExportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\File\FileSystemOperator;

class ImportExportController extends Controller
{
    /**
     * @Route("/import", name="oro_importexport_import_form")
     * @AclAncestor("oro_importexport_import")
     * @Template
     */
    public function importFormAction()
    {
        $entityName = $this->getRequest()->get('entity');

        $data = array();

        $importForm = $this->createForm(
            'oro_importexport_import',
            $data,
            array('entityName' => $entityName)
        );

        return array(
            'entityName' => $entityName,
            'form' => $importForm->createView()
        );
    }

    /**
     * @Route("/export/instant/{processorAlias}", name="oro_importexport_export_instant")
     * @AclAncestor("oro_importexport_import")
     */
    public function instantExportAction($processorAlias)
    {
        $fileName = $this->getFileSystemOperator()->generateTemporaryFileName($processorAlias, 'csv');
        $entityName = $this->getProcessorRegistry()->getProcessorEntityName(
            ProcessorRegistry::TYPE_EXPORT,
            $processorAlias
        );
        $configuration = array(
            'export' => array(
                'processorAlias' => $processorAlias,
                'entityName' => $entityName,
                'filePath' => $fileName,
            ),
        );

        $url = null;
        $messages = array();

        $jobResult = $this->getJobExecutor()->executeJob(
            ProcessorRegistry::TYPE_EXPORT,
            JobExecutor::JOB_EXPORT_TO_CSV,
            $configuration
        );

        if ($jobResult->isSuccessful()) {
            $url = $this->get('router')->generate(
                'oro_importexport_export_download',
                array('fileName' => basename($fileName))
            );
            foreach ($jobResult->getContexts() as $context) {
                $messages[] = array(
                    'type' => 'success',
                    'message' => $this->get('translator')->trans(
                        'oro_importexport.export.exported_entities_count %count%',
                        array('%count%' => $context->getReadCount())
                    ),
                );
            }
        } else {
            foreach ($jobResult->getErrors() as $error) {
                $messages[] = array('type' => 'error', 'message' => $error);
            }
        }

        return new JsonResponse(
            array(
                'success' => $jobResult->isSuccessful(),
                'url' => $url,
                'messages' => $messages
            )
        );
    }

    /**
     * @Route("/export/download/{fileName}", name="oro_importexport_export_download")
     * @AclAncestor("oro_importexport_export")
     */
    public function downloadExportResultAction($fileName)
    {
        $fullFileName = $this->getFileSystemOperator()->getTemporaryFile($fileName);

        $response = new BinaryFileResponse($fullFileName->getRealPath(), 200, array('Content-Type' => 'text/csv'));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    /**
     * @return ProcessorRegistry
     */
    protected function getProcessorRegistry()
    {
        return $this->get('oro_importexport.processor.registry');
    }

    /**
     * @return JobExecutor
     */
    protected function getJobExecutor()
    {
        return $this->get('oro_importexport.job_executor');
    }

    /**
     * @return FileSystemOperator
     */
    protected function getFileSystemOperator()
    {
        return $this->get('oro_importexport.file.file_system_operator');
    }
}
