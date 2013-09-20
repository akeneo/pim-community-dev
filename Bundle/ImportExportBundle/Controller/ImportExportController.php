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
use Doctrine\ORM\EntityManager;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\UserBundle\Annotation\Acl;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Oro\Bundle\BatchBundle\Job\BatchStatus;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\ImportExportBundle\Exception\RuntimeException;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;

class ImportExportController extends Controller
{
    const CONNECTOR_NAME = 'oro_importexport';

    const EXPORT_TO_CSV_JOB = 'entity_export_to_csv';


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

        return array(
            'entityName' => $entityName,
            'form' => $importForm->createView()
        );
    }

    /**
     * @Route("/export/instant/{entityName}/{processorAlias}", name="oro_importexport_export_instant")
     * @Acl(
     *      id="oro_importexport_export_instant",
     *      name="Instant entity export",
     *      description="Instant entity export",
     *      parent="oro_importexport"
     * )
     */
    public function instantExportAction($entityName, $processorAlias)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        /** @var ConnectorRegistry $jobRegistry */
        $jobRegistry = $this->get('oro_batch.connectors');
        /** @var ContextRegistry $contextRegistry */
        $contextRegistry = $this->get('oro_importexport.context_registry');
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        /** @var RouterInterface $router */
        $router = $this->get('router');

        $exportLabel = $this->generateTranslationLabel(
            ProcessorRegistry::TYPE_EXPORT,
            self::EXPORT_TO_CSV_JOB,
            $processorAlias
        );
        $fileName = $this->generateTmpFileName($processorAlias, 'csv');
        $configuration = array(
            'export' => array(
                'entityName' => $entityName,
                'processorAlias' => $processorAlias,
                'filePath' => $fileName,
            ),
        );

        $messages = array();
        $successful = false;
        $url = null;

        $entityManager->beginTransaction();
        try {
            $jobInstance = new JobInstance(
                self::CONNECTOR_NAME,
                ProcessorRegistry::TYPE_EXPORT,
                self::EXPORT_TO_CSV_JOB
            );
            $jobInstance->setCode(uniqid($processorAlias, true));
            $jobInstance->setLabel($exportLabel);
            $jobInstance->setRawConfiguration($configuration);
            $entityManager->persist($jobInstance);

            $job = $jobRegistry->getJob($jobInstance);
            if (!$job) {
                throw new RuntimeException(sprintf('Can\'t find job "%s"', self::EXPORT_TO_CSV_JOB));
            }

            $jobExecution = new JobExecution();
            $jobExecution->setJobInstance($jobInstance);
            $entityManager->persist($jobExecution);

            $job->execute($jobExecution);

            $entityManager->flush();

            if ($jobExecution->getStatus()->getValue() == BatchStatus::COMPLETED) {
                $entityManager->commit();
                $successful = true;
                $url = $router->generate(
                    'oro_importexport_export_download',
                    array('fileName' => basename($fileName))
                );
                foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                    $context = $contextRegistry->getByStepExecution($stepExecution);
                    $messages[] = array(
                        'type' => 'success',
                        'message' => $translator->trans(
                            'oro_importexport.export.exported_entities_count %count%',
                            array('%count%' => $context->getReadCount())
                        ),
                    );
                }
            } else {
                $entityManager->rollback();
                foreach ($jobExecution->getStepExecutions() as $stepExecution) {
                    $context = $contextRegistry->getByStepExecution($stepExecution);
                    foreach ($context->getErrors() as $error) {
                        $messages[] = array('type' => 'error', 'message' => $error);
                    }
                }
            }
        } catch (\Exception $e) {
            $entityManager->rollback();
            $messages[] = array('type' => 'error', 'message' => $e->getMessage());
        }

        return new JsonResponse(
            array(
                'successful' => $successful,
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
        $actionId = sprintf('%s.%s.%s.%s', self::CONNECTOR_NAME, $type, $job, $alias);

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

    protected function generateResponseFileName($fileName, $fileExtension)
    {
        return sprintf('%s.%s', preg_replace('~\W~', '_', $fileName), $fileExtension);
    }
}
