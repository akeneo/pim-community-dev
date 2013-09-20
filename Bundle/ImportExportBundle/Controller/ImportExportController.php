<?php

namespace Oro\Bundle\ImportExportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\RouterInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\ImportExportBundle\Job\JobExecutor;

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
     * @AclAncestor("oro_importexport_export")
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
}
