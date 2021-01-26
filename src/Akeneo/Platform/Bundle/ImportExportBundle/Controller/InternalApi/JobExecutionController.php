<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    protected JobExecutionArchivist $archivist;
    protected JobExecutionManager $jobExecutionManager;
    protected JobExecutionRepository $jobExecutionRepo;
    private NormalizerInterface $normalizer;

    public function __construct(
        TranslatorInterface $translator,
        JobExecutionArchivist $archivist,
        JobExecutionManager $jobExecutionManager,
        JobExecutionRepository $jobExecutionRepo,
        NormalizerInterface $normalizer
    ) {
        $this->translator = $translator;
        $this->archivist = $archivist;
        $this->jobExecutionManager = $jobExecutionManager;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->normalizer = $normalizer;
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

        $jobResponse['meta'] = [
            'logExists' => file_exists($jobExecution->getLogFile()),
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
