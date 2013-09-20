<?php

namespace Oro\Bundle\ImportExportBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;

class ImportExportController extends Controller
{
    const CONNECTOR_NAME = 'oro_importexport';

    const EXPORT_TO_CSV_JOB = 'entity_export_to_csv';


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
     * @Route("/export/instant/{entityName}/{processorAlias}", name="oro_importexport_export_instant")
     * @AclAncestor("oro_importexport_export")
     */
    public function instantExportAction($entityName, $processorAlias)
    {
        // TODO: transaction, result code processing

        $entityManager = $this->getDoctrine()->getManager();

        $exportLabel = $this->generateTranslationLabel(
            self::CONNECTOR_NAME,
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

        $jobInstance = new JobInstance(self::CONNECTOR_NAME, ProcessorRegistry::TYPE_EXPORT, self::EXPORT_TO_CSV_JOB);
        $jobInstance->setCode(uniqid($processorAlias, true));
        $jobInstance->setLabel($exportLabel);
        $jobInstance->setRawConfiguration($configuration);
        $entityManager->persist($jobInstance);

        /** @var ConnectorRegistry $jobRegistry */
        $jobRegistry = $this->get('oro_batch.connectors');
        $job = $jobRegistry->getJob($jobInstance);

        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);
        $entityManager->persist($jobExecution);

        $job->execute($jobExecution);

        $entityManager->flush();

        $responseFileName = $this->generateResponseFileName($processorAlias, 'csv');

        $response = new BinaryFileResponse($fileName, 200, array('Content-Type' => 'text/csv'));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $responseFileName);

        return $response;
    }

    protected function generateTranslationLabel($connector, $type, $job, $alias)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');
        $actionId = sprintf('%s.%s.%s.%s', $connector, $type, $job, $alias);

        return $translator->trans($actionId);
    }

    protected function generateTmpFileName($filePrefix, $fileExtension = 'tmp', $directory = 'import_export')
    {
        $cacheDir = rtrim($this->container->getParameter("kernel.cache_dir"), DIRECTORY_SEPARATOR);
        $importExportDir = $cacheDir . DIRECTORY_SEPARATOR . $directory;
        if (!file_exists($importExportDir) && !is_dir($importExportDir)) {
            mkdir($importExportDir, 0755);
        }

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
