<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Widget;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Model\JobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetJobExecutionTracking;
use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperationsInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Widget\LastOperationsFetcher;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LastOperationsFetcherSpec extends ObjectBehavior
{
    function let(
        GetLastOperationsInterface $query,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        PresenterInterface $presenter,
        GetJobExecutionTracking $getJobExecutionTracking,
        NormalizerInterface $jobExecutionTrackingNormalizer
    ) {
        $this->beConstructedWith(
            $query,
            $securityFacade,
            $tokenStorage,
            $presenter,
            $getJobExecutionTracking,
            $jobExecutionTrackingNormalizer
        );
    }

    function it_is_a_fetcher()
    {
        $this->shouldBeAnInstanceOf(LastOperationsFetcher::class);
    }

    function it_fetches_last_operations(
        $query,
        $tokenStorage,
        $presenter,
        UserInterface $user,
        TokenInterface $token,
        LocaleInterface $locale,
        JobExecutionTracking $jobExecutionTracking,
        GetJobExecutionTracking $getJobExecutionTracking,
        NormalizerInterface $jobExecutionTrackingNormalizer
    ) {
        $date = '2019-12-01 12:00:00';
        $operation = [
            'date'   => $date,
            'type'   => 'import',
            'label'  => 'My import',
            'status' => 1,
            'id'     => 3
        ];

        $jobExecutionTrackingNormalized = [
            "error" => false,
            "warning" => false,
            "status" => "COMPLETED",
            "currentStep" => 1,
            "totalSteps" => 1,
            "steps" => [
                [
                    "jobName" => "csv_product_export",
                    "stepName" => "export",
                    "status" => "COMPLETED",
                    "isTrackable" => true,
                    "hasWarning" => false,
                    "hasError" => false,
                    "duration" => 1,
                    "processedItems" => 0,
                    "totalItems" => 0
                ]
            ]
        ];

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $query->execute($user)->willReturn([$operation]);

        $getJobExecutionTracking->execute(3)->willReturn($jobExecutionTracking);
        $user->getUiLocale()->willReturn($locale);
        $user->getTimezone()->willReturn('Pacific/Kiritimati');
        $locale->getCode()->willReturn('fr_FR');
        $presenter->present(
            new \DateTime($date, new \DateTimeZone('UTC')),
            ['locale' => 'fr_FR', 'timezone' => 'Pacific/Kiritimati']
        )->willReturn('02/12/2019 02:00:00');
        $jobExecutionTrackingNormalizer->normalize($jobExecutionTracking)->willReturn($jobExecutionTrackingNormalized);

        $operation['statusLabel'] = 'pim_import_export.batch_status.1';
        $operation['date'] = '02/12/2019 02:00:00';
        $operation['tracking'] = $jobExecutionTrackingNormalized;
        $operation['canSeeReport'] = false;

        $this->fetch()->shouldReturn([$operation]);
    }
}
