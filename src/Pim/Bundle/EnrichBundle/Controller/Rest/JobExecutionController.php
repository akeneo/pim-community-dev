<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Bundle\BatchBundle\Manager\JobExecutionManager;
use Pim\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\JobExecutionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Channel controller
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionController
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var JobExecutionArchivist */
    protected $archivist;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var JobExecutionManager */
    protected $jobExecutionManager;

    /** @var JobExecutionRepository */
    protected $jobExecutionRepo;

    /**
     * @param TranslatorInterface      $translator
     * @param JobExecutionArchivist    $archivist
     * @param SerializerInterface      $serializer
     * @param JobExecutionManager      $jobExecutionManager
     * @param JobExecutionRepository   $jobExecutionRepo
     */
    public function __construct(
        TranslatorInterface $translator,
        JobExecutionArchivist $archivist,
        SerializerInterface $serializer,
        JobExecutionManager $jobExecutionManager,
        JobExecutionRepository $jobExecutionRepo
    ) {
        $this->translator = $translator;
        $this->archivist = $archivist;
        $this->serializer = $serializer;
        $this->jobExecutionManager = $jobExecutionManager;
        $this->jobExecutionRepo = $jobExecutionRepo;
    }

    /**
     * Get jobs
     * @param $id
     * @return JsonResponse
     */
    public function getAction($id){
        $jobExecution = $this->jobExecutionRepo->find($id);
        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Component\Batch\Model\JobExecution entity not found');
        }

        $archives = [];
        foreach ($this->archivist->getArchives($jobExecution) as $archiveName => $files) {
            $label = $this->translator->transChoice(
                sprintf('pim_mass_edit.download_archive.%s', $archiveName),
                count($files)
            );
            $archives[$archiveName] = [
                'label' => ucfirst($label),
                'files' => $files,
            ];
        }

        if (!$this->jobExecutionManager->checkRunningStatus($jobExecution)) {
            $this->jobExecutionManager->markAsFailed($jobExecution);
        }

        // limit the number of step execution returned to avoid memory overflow
        $context = ['limit_warnings' => 100];


        $jobResponse = $this->serializer->normalize($jobExecution, 'standard', $context);
        $jobResponse['meta'] = [
            "log"           => file_exists($jobExecution->getLogFile()),
            "archives"      => $archives,
            "jobId"         => $id
        ];
        return new JsonResponse($jobResponse);
    }
}
