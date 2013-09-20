<?php

namespace Oro\Bundle\ImportExportBundle\Controller;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\RouterInterface;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\Rest\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;

class ImportExportController extends FOSRestController
{
    /**
     * @Route("/import", name="oro_importexport_import_form")
     * @Acl(
     *      id="oro_importexport_import",
     *      name="Entity import form",
     *      description="Entity import form",
     *      parent="oro_importexport"
     * )
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

                $tmpFileName = $this->generateTmpFileName($processorAlias, 'csv');
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
        /** @var ProcessorRegistry $processorRegistry */
        $processorRegistry = $this->get('oro_importexport.processor.registry');
        /** @var JobExecutor $jobExecutor */
        $jobExecutor = $this->get('oro_importexport.job_executor');

        $fileName = $this->get('session')->get($this->getImportFileSessionKey($processorAlias));
        if (!$fileName || !file_exists($fileName)) {
            throw new \Exception('No file in session');
        }

        $entityName = $processorRegistry->getProcessorEntityName(
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

        $jobResult = $jobExecutor->executeJob(
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

        /** @var ContextInterface[] $contexts */
        $contexts = $jobResult->getContexts();
        $counts = array();
        if (isset($contexts[0])) {
            $counts['read'] = $contexts[0]->getReadCount();
            $counts['add'] = $contexts[0]->getAddCount();
            $counts['replace'] = $contexts[0]->getReplaceCount();
            $counts['update'] = $contexts[0]->getUpdateCount();
            $counts['delete'] = $contexts[0]->getDeleteCount();
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
     * @Acl(
     *      id="oro_importexport_export_instant",
     *      name="Instant entity export",
     *      description="Instant entity export",
     *      parent="oro_importexport"
     * )
     */
    public function instantExportAction($processorAlias)
    {
        /** @var ProcessorRegistry $processorRegistry */
        $processorRegistry = $this->get('oro_importexport.processor.registry');
        /** @var JobExecutor $jobExecutor */
        $jobExecutor = $this->get('oro_importexport.job_executor');
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        /** @var RouterInterface $router */
        $router = $this->get('router');

        $fileName = $this->generateTmpFileName($processorAlias, 'csv');
        $entityName = $processorRegistry->getProcessorEntityName(ProcessorRegistry::TYPE_EXPORT, $processorAlias);
        $configuration = array(
            'export' => array(
                'processorAlias' => $processorAlias,
                'entityName' => $entityName,
                'filePath' => $fileName,
            ),
        );

        $url = null;
        $messages = array();

        $jobResult = $jobExecutor->executeJob(
            ProcessorRegistry::TYPE_EXPORT,
            JobExecutor::JOB_EXPORT_TO_CSV,
            $configuration
        );

        if ($jobResult->isSuccessful()) {
            $url = $router->generate(
                'oro_importexport_export_download',
                array('fileName' => basename($fileName))
            );
            foreach ($jobResult->getContexts() as $context) {
                $messages[] = array(
                    'type' => 'success',
                    'message' => $translator->trans(
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
                'successful' => $jobResult->isSuccessful(),
                'url' => $url,
                'messages' => $messages
            )
        );
    }

    /**
     * @Route("/export/download/{fileName}", name="oro_importexport_export_download")
     * @Acl(
     *      id="oro_importexport_export_download",
     *      name="Download export result file",
     *      description="Download export result file",
     *      parent="oro_importexport"
     * )
     */
    public function downloadExportResultAction($fileName)
    {
        $fullFileName = $this->getFullFileName($fileName);

        $response = new BinaryFileResponse($fullFileName, 200, array('Content-Type' => 'text/csv'));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    protected function generateTranslationLabel($type, $job, $alias)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        $actionId = sprintf('%s.%s.%s.%s', JobExecutor::CONNECTOR_NAME, $type, $job, $alias);

        return $translator->trans($actionId);
    }

    protected function generateTmpFileName($filePrefix, $fileExtension = 'tmp')
    {
        $importExportDir = $this->getImportExportTmpDir();

        do {
            $fileName = sprintf(
                '%s%s%s.%s',
                $importExportDir,
                DIRECTORY_SEPARATOR,
                preg_replace('~\W~', '_', uniqid($filePrefix . '_', true)),
                $fileExtension
            );
        } while (file_exists($fileName));

        return $fileName;
    }

    protected function getImportExportTmpDir()
    {
        $cacheDir = rtrim($this->container->getParameter("kernel.cache_dir"), DIRECTORY_SEPARATOR);
        $importExportDir = $cacheDir . DIRECTORY_SEPARATOR . 'import_export';
        if (!file_exists($importExportDir) && !is_dir($importExportDir)) {
            mkdir($importExportDir);
        }

        if (!is_readable($importExportDir)) {
            throw new \LogicException('Import/export directory is not readable');
        }
        if (!is_writable($importExportDir)) {
            throw new \LogicException('Import/export directory is not writeable');
        }

        return $importExportDir;
    }

    protected function getFullFileName($fileName)
    {
        $importExportDir = $this->getImportExportTmpDir();
        $fullFileName = $importExportDir . DIRECTORY_SEPARATOR . $fileName;
        if (!file_exists($fullFileName) || !is_file($fullFileName) || !is_readable($fullFileName)) {
            throw new \LogicException(sprintf('Can\'t read file %s', $fileName));
        }

        return $fullFileName;
    }
}
