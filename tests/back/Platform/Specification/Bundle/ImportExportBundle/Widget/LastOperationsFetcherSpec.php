<?php

namespace Specification\Akeneo\Platform\Bundle\ImportExportBundle\Widget;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Query\GetLastOperationsInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Widget\LastOperationsFetcher;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LastOperationsFetcherSpec extends ObjectBehavior
{
    function let(
        GetLastOperationsInterface $query,
        SecurityFacade $securityFacade,
        TokenStorageInterface $tokenStorage,
        PresenterInterface $presenter
    ) {
        $this->beConstructedWith($query, $securityFacade, $tokenStorage, $presenter);
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
        LocaleInterface $locale
    ) {
        $date = '2019-12-01 12:00:00';
        $operation = [
            'date'   => $date,
            'type'   => 'import',
            'label'  => 'My import',
            'status' => 1,
            'id'     => 3
        ];

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $query->execute($user)->willReturn([$operation]);

        $user->getUiLocale()->willReturn($locale);
        $user->getTimezone()->willReturn('Pacific/Kiritimati');
        $locale->getCode()->willReturn('fr_FR');
        $presenter->present(
            new \DateTime($date, new \DateTimeZone('UTC')),
            ['locale' => 'fr_FR', 'timezone' => 'Pacific/Kiritimati']
        )->willReturn('02/12/2019 02:00:00');

        $operation['statusLabel'] = 'pim_import_export.batch_status.1';
        $operation['date'] = '02/12/2019 02:00:00';
        $operation['canSeeReport'] = false;

        $this->fetch()->shouldReturn([$operation]);
    }
}
