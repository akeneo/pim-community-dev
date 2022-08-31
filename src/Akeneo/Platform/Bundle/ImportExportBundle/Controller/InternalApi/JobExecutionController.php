<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Connector\LogKey;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionController
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var JobExecutionArchivist */
    protected $archivist;

    /** @var JobExecutionManager */
    protected $jobExecutionManager;

    /** @var JobExecutionRepository */
    protected $jobExecutionRepo;

    /** @var NormalizerInterface */
    private $normalizer;

    // @todo pull-up-to-6.0 remove these line
    /** @var FilesystemInterface|null */
    private $logFileSystem;

    public function __construct(
        TranslatorInterface $translator,
        JobExecutionArchivist $archivist,
        JobExecutionManager $jobExecutionManager,
        JobExecutionRepository $jobExecutionRepo,
        NormalizerInterface $normalizer,
        FilesystemInterface $logFilesystem,
        // @todo pull-up-to-6.0 remove this line
        ?FilesystemInterface $logFileSystem = null
    ) {
        $this->translator = $translator;
        $this->archivist = $archivist;
        $this->jobExecutionManager = $jobExecutionManager;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->normalizer = $normalizer;
        // @todo pull-up-to-6.0 remove this line
        $this->logFileSystem = $logFilesystem;
    }

    public function getAction($identifier): JsonResponse
    {
        $jobExecution = $this->jobExecutionRepo->find($identifier);
        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }

        $jobExecution = $this->jobExecutionManager->resolveJobExecutionStatus($jobExecution);

        $context = ['limit_warnings' => 100];

        $jobResponse = $this->normalizer->normalize($jobExecution, 'internal_api', $context);

        // @todo pull-up-to-6.0 remove these line
        if (null === $this->logFileSystem) {
            $logExists = file_exists($jobExecution->getLogFile());
        } else {
            $logExists = !empty($jobExecution->getLogFile()) && $this->logFileSystem->has(new LogKey($jobExecution));
        }

        $jobResponse['meta'] = [
            // @todo pull-up-to-6.0 remove this line
            'logExists' => $logExists,
            'archives' => $this->archives($jobExecution),
            'id' => $identifier,
        ];

        return new JsonResponse($jobResponse);
    }

    private function archives(JobExecution $jobExecution): array
    {
        $archives = [];
        foreach ($this->archivist->getArchives($jobExecution) as $archiveName => $files) {
            $label = $this->translator->trans(sprintf('pim_enrich.entity.job_execution.module.download.%s', $archiveName));
            $archives[$archiveName] = [
                'label' => $label,
                'files' => $files,
            ];
        }

        return $archives;
    }
}
