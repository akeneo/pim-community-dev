<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
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
    protected JobExecutionArchivist $archivist;
    protected JobExecutionManager $jobExecutionManager;
    protected JobExecutionRepository $jobExecutionRepo;
    private NormalizerInterface $normalizer;
    private SecurityFacade $securityFacade;
    private array $jobSecurityMapping;

    public function __construct(
        TranslatorInterface $translator,
        JobExecutionArchivist $archivist,
        JobExecutionManager $jobExecutionManager,
        JobExecutionRepository $jobExecutionRepo,
        NormalizerInterface $normalizer,
        SecurityFacade $securityFacade,
        array $jobSecurityMapping
    ) {
        $this->translator = $translator;
        $this->archivist = $archivist;
        $this->jobExecutionManager = $jobExecutionManager;
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->normalizer = $normalizer;
        $this->securityFacade = $securityFacade;
        $this->jobSecurityMapping = $jobSecurityMapping;
    }

    public function getAction($identifier): JsonResponse
    {
        // TODO: remove this line when upgrading flysystem library
        /* at the moment the listing of the archives can be very slow if
            - there are a lot of files for a given job execution (>100000)
            - the storage is Google Cloud Storage
          because the GS adapter we currently use ignores the $recursive option and returns every file in the bucket
          for the given path.
          The league/flysystem-google-cloud-storage seems to handle this case, but it's only compatible with flysystem v2
        */
        \set_time_limit(0);

        /** @var JobExecution $jobExecution */
        $jobExecution = $this->jobExecutionRepo->find($identifier);
        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }

        if (!$this->isJobGranted($jobExecution)) {
            throw new AccessDeniedException();
        }

        $jobExecution = $this->jobExecutionManager->resolveJobExecutionStatus($jobExecution);

        $context = ['limit_warnings' => 100];

        $jobResponse = $this->normalizer->normalize($jobExecution, 'internal_api', $context);

        $jobResponse['meta'] = [
            'logExists' => file_exists($jobExecution->getLogFile()),
            'archives' => $this->archives($jobExecution),
            'generateZipArchive' => $this->archivist->hasAtLeastTwoArchives($jobExecution),
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

    private function isJobGranted(JobExecution $jobExecution): bool
    {
        $jobExecutionType = $jobExecution->getJobInstance()->getType();
        if (!array_key_exists($jobExecutionType, $this->jobSecurityMapping)) {
            return true;
        }

        return $this->securityFacade->isGranted($this->jobSecurityMapping[$jobExecutionType]);
    }
}
