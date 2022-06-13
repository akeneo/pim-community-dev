<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\ConnectedPimUserProviderInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppAuthenticationScopesActionSpec extends ObjectBehavior
{
    public function let(
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery,
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery,
        ConnectedPimUserProviderInterface $connectedPimUserProvider,
    ): void {
        $findOneConnectedAppByConnectionCodeQuery->execute('app_connection_code')->willReturn(
            new ConnectedApp(
                'app_identifier',
                'my app',
                ['foo', 'bar'],
                'app_connection_code',
                'app_logo',
                'app_author',
                'app_123456abcdef',
                'an_username',
            )
        );

        $connectedPimUserProvider->getCurrentUserId()->willReturn(42);

        $getUserConsentedAuthenticationScopesQuery->execute(42, 'app_identifier')->willReturn([
            'auth_scope_a',
            'auth_scope_b',
            'auth_scope_c'
        ]);

        $this->beConstructedWith(
            $getUserConsentedAuthenticationScopesQuery,
            $findOneConnectedAppByConnectionCodeQuery,
            $connectedPimUserProvider,
        );
    }

    public function it_returns_a_list_of_authentication_scopes(): void
    {
        $this->__invoke('app_connection_code')->shouldBeLike(new JsonResponse([
            'auth_scope_a',
            'auth_scope_b',
            'auth_scope_c'
        ]));
    }

    public function it_returns_an_empty_list_of_authentication_scopes(
        GetUserConsentedAuthenticationScopesQueryInterface $getUserConsentedAuthenticationScopesQuery
    ): void {
        $getUserConsentedAuthenticationScopesQuery->execute(42, 'app_identifier')->willReturn([]);

        $this->__invoke('app_connection_code')->shouldBeLike(new JsonResponse([]));
    }

    public function it_throws_a_not_found_exception_on_non_connected_app_connection_code(
        FindOneConnectedAppByConnectionCodeQueryInterface $findOneConnectedAppByConnectionCodeQuery
    ): void {
        $findOneConnectedAppByConnectionCodeQuery->execute('foo')->willReturn(null);

        $this
            ->shouldThrow(new NotFoundHttpException('Connected app with connection code foo does not exist.'))
            ->during('__invoke', ['foo']);
    }
}
