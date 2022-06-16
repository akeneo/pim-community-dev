<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Widget;

use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetJobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperationsInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LastOperationsFetcher
{
    /** @var GetLastOperationsInterface */
    protected $lastOperations;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var PresenterInterface */
    private $presenter;
    /** @var NormalizerInterface */
    private $jobExecutionTrackingNormalizer;
    /** @var GetJobExecutionTracking */
    private $getJobExecutionTracking;

    public function __construct(
        GetLastOperationsInterface $lastOperations,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        PresenterInterface $presenter,
        GetJobExecutionTracking $getJobExecutionTracking,
        NormalizerInterface $jobExecutionTrackingNormalizer
    ) {
        $this->lastOperations = $lastOperations;
        $this->securityFacade = $securityFacade;
        $this->tokenStorage = $tokenStorage;
        $this->presenter = $presenter;
        $this->getJobExecutionTracking = $getJobExecutionTracking;
        $this->jobExecutionTrackingNormalizer = $jobExecutionTrackingNormalizer;
    }

    public function fetch(): array
    {
        $user = $this->getUser();
        $canViewAllJobs = $this->securityFacade->isGranted('pim_enrich_job_tracker_view_all_jobs');
        $operations = $this->lastOperations->execute($canViewAllJobs ? null : $user);

        $timezone = $user->getTimeZone();
        $locale = $user->getUiLocale()->getCode();
        foreach ($operations as &$operation) {
            $operation['statusLabel'] = sprintf(
                'pim_import_export.batch_status.%d',
                $operation['status']
            );

            $date = $operation['date'] ?? null;
            if (is_string($date)) {
                $operation['date'] = $this->presenter->present(
                    new \DateTime($date, new \DateTimeZone('UTC')),
                    [
                        'locale' => $locale,
                        'timezone' => $timezone,
                    ]
                );
            }
            $operation['tracking'] = $this->jobExecutionTrackingNormalizer->normalize(
                $this->getJobExecutionTracking->execute((int) $operation['id'])
            );
            $operation['canSeeReport'] = !in_array($operation['type'], ['import', 'export']) ||
                $this->securityFacade->isGranted(sprintf('pim_importexport_%s_execution_show', $operation['type']));
        }

        return $operations;
    }

    private function getUser(): UserInterface
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('The User object must implement the UserInterface interface.');
        }

        return $user;
    }
}
