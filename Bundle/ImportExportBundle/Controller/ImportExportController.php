<?php

namespace Oro\Bundle\ImportExportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
use Oro\Bundle\ImportExportBundle\Form\Model\ImportData;

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

        $importForm = $this->createForm('oro_importexport_import', null, array('entityName' => $entityName));

        if ($this->getRequest()->isMethod('POST')) {
            $importForm->submit($this->getRequest());

            if ($importForm->isValid()) {
                /** @var ImportData $data */
                $data = $importForm->getData();
                $file = $data->getFile();
                $processorAlias = $data->getProcessorAlias();

                $tmpFileName = $this->getFileSystemOperator()->generateTemporaryFileName($processorAlias, 'csv');
                $file->move(dirname($tmpFileName), basename($tmpFileName));

                $this->setImportFileName($processorAlias, $tmpFileName);
                return $this->forward(
                    'OroImportExportBundle:ImportExport:importValidate',
                    array('processorAlias' => $processorAlias)
                );
            }
        }

        return array(
            'entityName' => $entityName,
            'form' => $importForm->createView()
        );
    }

    /**
     * @Route("/import/validate/{processorAlias}", name="oro_importexport_import_validate")
     * @AclAncestor("oro_importexport_import")
     * @Template
     */
    public function importValidateAction($processorAlias)
    {
        $entityName = $this->getProcessorRegistry()->getProcessorEntityName(
            ProcessorRegistry::TYPE_IMPORT_VALIDATION,
            $processorAlias
        );
        $configuration = array(
            'import_validation' => array(
                'processorAlias' => $processorAlias,
                'entityName' => $entityName,
                'filePath' => $this->getImportFileName($processorAlias),
            ),
        );

        $jobResult = $this->getJobExecutor()->executeJob(
            ProcessorRegistry::TYPE_IMPORT_VALIDATION,
            JobExecutor::JOB_VALIDATE_IMPORT_FROM_CSV,
            $configuration
        );

        /** @var ContextInterface $contexts */
        $context = $jobResult->getContext();
        $counts = array();
        if ($context) {
            $counts['read'] = $context->getReadCount();
            $counts['add'] = $context->getAddCount();
            $counts['replace'] = $context->getReplaceCount();
            $counts['update'] = $context->getUpdateCount();
            $counts['delete'] = $context->getDeleteCount();
        }
        $counts['errors'] = count($jobResult->getErrors());

        $errorsUrl = null;
        $errors = array();
        if (!empty($counts['errors'])) {
            $errorsUrl = $this->get('router')->generate(
                'oro_importexport_error_log',
                array('jobCode' => $jobResult->getJobCode())
            );
            $errors = array_slice($jobResult->getErrors(), 0, 100);
        }

        return array(
            'isSuccessful' => $jobResult->isSuccessful(),
            'processorAlias' => $processorAlias,
            'counts' => $counts,
            'errorsUrl' => $errorsUrl,
            'errors' => $errors,
            'entityName' => $entityName,
            'memory_usage' => memory_get_usage(true)
        );
    }

    /**
     * @Route("/import/process/{processorAlias}", name="oro_importexport_import_process")
     * @AclAncestor("oro_importexport_export")
     *
     * @param string $processorAlias
     * @return Response
     */
    public function importProcessAction($processorAlias)
    {
        $entityName = $this->getProcessorRegistry()->getProcessorEntityName(
            ProcessorRegistry::TYPE_IMPORT,
            $processorAlias
        );
        $configuration = array(
            'import' => array(
                'processorAlias' => $processorAlias,
                'entityName' => $entityName,
                'filePath' => $this->getImportFileName($processorAlias),
            ),
        );

        $jobResult = $this->getJobExecutor()->executeJob(
            ProcessorRegistry::TYPE_IMPORT,
            JobExecutor::JOB_IMPORT_FROM_CSV,
            $configuration
        );

        if ($jobResult->isSuccessful()) {
            $this->removeImportFileName($processorAlias);
            $message = $this->get('translator')->trans('oro_importexport.import.import_success');
        } else {
            $message = $this->get('translator')->trans('oro_importexport.import.import_error');
        }

        $errorsUrl = null;
        if ($jobResult->getErrors()) {
            $errorsUrl = $this->get('router')->generate(
                'oro_importexport_error_log',
                array('jobCode' => $jobResult->getJobCode())
            );
        }

        return new JsonResponse(
            array(
                'success' => $jobResult->isSuccessful(),
                'message' => $message,
                'errorsUrl' => $errorsUrl,
                'memory_usage' => memory_get_usage(true)
            )
        );
    }

    /**
     * @Route("/export/instant/{processorAlias}", name="oro_importexport_export_instant")
     * @AclAncestor("oro_importexport_export")
     *
     * @param string $processorAlias
     * @return Response
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
        $errorsCount = 0;
        $readsCount = 0;

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
            $context = $jobResult->getContext();
            if ($context) {
                $readsCount = $context->getReadCount();
            }
        } else {
            $url = $this->get('router')->generate(
                'oro_importexport_error_log',
                array('jobCode' => $jobResult->getJobCode())
            );
            $errorsCount = count($jobResult->getErrors());
        }

        return new JsonResponse(
            array(
                'success' => $jobResult->isSuccessful(),
                'url' => $url,
                'readsCount' => $readsCount,
                'errorsCount' => $errorsCount,
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
     * @Route("/import_export/error/{jobCode}.log", name="oro_importexport_error_log")
     * @AclAncestor("oro_importexport")
     */
    public function errorLogAction($jobCode)
    {
        $errors = $this->getJobExecutor()->getJobErrors($jobCode);
        $content = implode("\r\n", $errors);

        return new Response($content, 200, array('Content-Type' => 'text/x-log'));
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

    /**
     * @param string $processorAlias
     * @param string $fileName
     */
    protected function setImportFileName($processorAlias, $fileName)
    {
        $this->get('session')->set($this->getImportFileSessionKey($processorAlias), $fileName);
    }

    /**
     * @param string $processorAlias
     */
    protected function removeImportFileName($processorAlias)
    {
        $this->get('session')->remove($this->getImportFileSessionKey($processorAlias));
    }

    /**
     * @param string $processorAlias
     * @return mixed
     * @throws BadRequestHttpException
     */
    protected function getImportFileName($processorAlias)
    {
        $fileName = $this->get('session')->get($this->getImportFileSessionKey($processorAlias));
        if (!$fileName || !file_exists($fileName)) {
            throw new BadRequestHttpException('No file to import');
        }
        return $fileName;
    }

    /**
     * @param string $alias
     * @return string
     */
    protected function getImportFileSessionKey($alias)
    {
        return 'oro_importexport_import_' . $alias;
    }
}
