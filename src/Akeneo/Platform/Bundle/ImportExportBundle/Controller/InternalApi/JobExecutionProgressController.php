<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi;

use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Tool\Bundle\BatchQueueBundle\Manager\JobExecutionManager;
use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionArchivist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionProgressController
{
    /** @var JobExecutionRepository */
    protected $jobExecutionRepo;

    /**
     * @var JobExecutionManager
     */
    private $jobExecutionManager;

    public function __construct(
        JobExecutionManager $jobExecutionManager,
        JobExecutionRepository $jobExecutionRepo
    ) {
        $this->jobExecutionRepo = $jobExecutionRepo;
        $this->jobExecutionManager = $jobExecutionManager;
    }

    /**
     * Get jobs
     *
     * @param $identifier
     *
     * @return JsonResponse
     */
    public function getAction($jobId)
    {
        $jobExecution = $this->jobExecutionRepo->find($jobId);
        if (null === $jobExecution) {
            throw new NotFoundHttpException('Akeneo\Tool\Component\Batch\Model\JobExecution entity not found');
        }
        $jobExecution = $this->jobExecutionManager->resolveJobExecutionStatus($jobExecution);

        $response = [
            'status' => $jobExecution->getStatus()->getValue(),
            'currentStep' => 1,
            'totalSteps' => 3,
        ];

        return new JsonResponse($response);
    }
}
