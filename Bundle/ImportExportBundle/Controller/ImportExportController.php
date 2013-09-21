<?php

namespace Oro\Bundle\ImportExportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\Rest\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\ImportExportBundle\File\FileSystemOperator;

class ImportExportController extends Controller
{
    const MAX_ERRORS_COUNT = 3;

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

        if ($this->getRequest()->isMethod('POST')) {
            $importForm->submit($this->getRequest());

            if ($importForm->isValid()) {
                $data = $importForm->getData();
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $data['file'];
                $processorAlias = $data['processor'];

                $tmpFileName = $this->getFileSystemOperator()->generateTemporaryFileName($processorAlias, 'csv');
                $uploadedFile->move(dirname($tmpFileName), basename($tmpFileName));

                $this->get('session')->set($this->getImportFileSessionKey($processorAlias), $tmpFileName);
                return $this->forward(
                    'OroImportExportBundle:ImportExport:importValidate',
                    array('processorAlias' => $processorAlias)
                );
            } else {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('Invalid data'));
            }
        }

        return array(
            'entityName' => $entityName,
            'form' => $importForm->createView()
        );
    }

    protected function getImportFileSessionKey($alias)
    {
        return 'oro_importexport_import_' . $alias;
    }

//    /**
//     * @Rest\Get("/import/validate/{processorAlias}", defaults={"_format"="json"})
//     * @ApiDoc(description="Validate entity import", resource=true)
//     * @AclAncestor("oro_importexport_import")
//     *
//     * @param string $processorAlias
//     * @return Response
//     */
    /**
     * @Route("/import/validate/{processorAlias}", name="oro_importexport_import_validate")
     * @AclAncestor("oro_importexport_import")
     * @Template
     */
    public function importValidateAction($processorAlias)
    {
        $fileName = $this->get('session')->get($this->getImportFileSessionKey($processorAlias));
        if (!$fileName || !file_exists($fileName)) {
            throw new \Exception('No file in session');
        }

        $entityName = $this->getProcessorRegistry()->getProcessorEntityName(
            ProcessorRegistry::TYPE_IMPORT_VALIDATION,
            $processorAlias
        );
        $configuration = array(
            'import_validation' => array(
                'processorAlias' => $processorAlias,
                'entityName' => $entityName,
                'filePath' => $fileName,
            ),
        );

        $jobResult = $this->getJobExecutor()->executeJob(
            ProcessorRegistry::TYPE_IMPORT_VALIDATION,
            JobExecutor::JOB_VALIDATE_IMPORT_FROM_CSV,
            $configuration
        );

        $errors = array();
        if (!$jobResult->isSuccessful()) {
            foreach ($jobResult->getErrors() as $error) {
                $errors[] = array('type' => 'error', 'message' => $error);
            }
        }

        /** @var ContextInterface $contexts */
        $context = $jobResult->getContext();
        $counts = array();
        if (isset($contexts[0])) {
            $counts['read'] = $context->getReadCount();
            $counts['add'] = $context->getAddCount();
            $counts['replace'] = $context->getReplaceCount();
            $counts['update'] = $context->getUpdateCount();
            $counts['delete'] = $context->getDeleteCount();
        }

        return array(
            'isSuccessful' => $jobResult->isSuccessful(),
            'processorAlias' => $processorAlias,
            'counts' => $counts,
            'errors' => $errors
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
            $messages[] = array(
                'type' => 'success',
                'message' => $this->get('translator')->trans(
                    'oro_importexport.export.exported_entities_count %count%',
                    array('%count%' => $jobResult->getContext()->getReadCount())
                ),
            );
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
