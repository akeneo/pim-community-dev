<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\StepExecutionArchivist;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Connector\LogKey;
use League\Flysystem\FilesystemReader;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionController
{
    protected TranslatorInterface $translator;
    protected StepExecutionArchivist $archivist;
    protected JobExecutionManager $jobExecutionManager;
    protected JobExecutionRepository $jobExecutionRepo;
    private NormalizerInterface $normalizer;
    private SecurityFacade $securityFacade;
    private array $jobSecurityMapping;
    private FilesystemReader $logFilesystem;

    public function __construct(
        TranslatorInterface $translator,
        StepExecutionArchivist $archivist,
        JobExecutionManager $jobExecutionManager,
        JobExecutionRepository $jobExecutionRepo,
        NormalizerInterface $normalizer,
        SecurityFacade $securityFacade,
        FilesystemReader $logFilesystem,
        array $jobSecurityMapping
    ) {
        $this->translator = $translator;
        $this->archivist = $archivist;
        $this->jobExecutionManager = $jobExecutionManager;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->normalizer = $normalizer;
        $this->securityFacade = $securityFacade;
        $this->jobSecurityMapping = $jobSecurityMapping;
        $this->logFilesystem = $logFilesystem;
    }

    public function getAction($identifier): JsonResponse
    {
        /** @var JobExecution|null $jobExecution */
        $jobExecution = $this->jobExecutionRepo->find($identifier);
        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }

        if (!$this->isJobGranted($jobExecution)) {
            throw new AccessDeniedException();
        }

        $jobExecution = $this->jobExecutionManager->resolveJobExecutionStatus($jobExecution);

        $context = ['limit_warnings' => 100];

        $archives = $this->archives($jobExecution);
        $generateZipArchive = $this->archivist->hasAtLeastTwoArchives($jobExecution);

        $jobResponse = $this->normalizer->normalize($jobExecution, 'internal_api', $context);

        $jobResponse['meta'] = [
            'logExists' => !empty($jobExecution->getLogFile()) && $this->logFilesystem->fileExists(new LogKey($jobExecution)),
            'archives' => $archives,
            'generateZipArchive' => $generateZipArchive,
            'id' => $identifier,
        ];

        return new JsonResponse($jobResponse);
    }

    private function archives(JobExecution $jobExecution): array
    {
        $archives = [];
        foreach ($this->archivist->getArchives($jobExecution) as $archiveName => $files) {
            $label = $this->translator->trans(sprintf('pim_enrich.entity.job_execution.module.download.%s', $archiveName));
            if (!\is_array($files)) {
                $files = \iterator_to_array($files);
            }
            if (\count($files) > 0) {
                $archives[$archiveName] = [
                    'label' => $label,
                    'files' => $files,
                ];
            }
        }

        return $archives;
    }

    private function isJobGranted(JobExecution $jobExecution): bool
    {
        $jobExecutionType = $jobExecution->getJobInstance()->getType();
        if (!array_key_exists($jobExecutionType, $this->jobSecurityMapping)) {
            return true;
        }

        return $this->securityFacade->isGranted($this->jobSecurityMapping[$jobExecutionType]);
    }
}
