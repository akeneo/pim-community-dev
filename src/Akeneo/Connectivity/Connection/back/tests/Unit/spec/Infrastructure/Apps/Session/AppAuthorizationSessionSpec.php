<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Session;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Session\AppAuthorizationSession;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AppAuthorizationSessionSpec extends ObjectBehavior
{
    public function let(SessionInterface $session): void
    {
        $this->beConstructedWith($session);
    }

    public function it_is_an_app_authorization_session(): void
    {
        $this->shouldHaveType(AppAuthorizationSession::class);
    }

    public function it_adds_in_the_session_the_app(SessionInterface $session): void
    {
        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
            'authorization_scope' => 'write_catalog_structure delete_products read_association_types',
            'authentication_scope' => 'openid profile email',
            'redirect_uri' => 'http://example.com',
            'state' => 'foo',
        ]);
        $session->set(
            '_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715',
            \json_encode($appAuthorization->normalize(), JSON_THROW_ON_ERROR)
        );

        $this->initialize($appAuthorization);
    }

    public function it_retrieves_an_app_from_the_session_given_an_app_client_id(SessionInterface $session): void
    {
        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
            'authorization_scope' => 'write_catalog_structure delete_products read_association_types',
            'authentication_scope' => 'openid profile email',
            'redirect_uri' => 'http://example.com',
            'state' => 'foo',
        ]);
        $session->get('_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715')->willReturn(\json_encode([
            'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
            'authorization_scope' => 'write_catalog_structure delete_products read_association_types',
            'authentication_scope' => 'openid profile email',
            'redirect_uri' => 'http://example.com',
            'state' => 'foo',
        ]));

        $this->getAppAuthorization($appAuthorization->clientId)->shouldBeLike($appAuthorization);
    }

    public function it_returns_null_if_no_app_has_been_initialized_before(SessionInterface $session): void
    {
        $session->get('_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715')->willReturn(null);

        $this->getAppAuthorization('90741597-54c5-48a1-98da-a68e7ee0a715')->shouldReturn(null);
    }
}
