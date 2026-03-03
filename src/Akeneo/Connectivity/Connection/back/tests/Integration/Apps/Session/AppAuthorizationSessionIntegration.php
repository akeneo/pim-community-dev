<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Session;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Session\AppAuthorizationSession;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AppAuthorizationSessionIntegration extends TestCase
{
    private AppAuthorizationSession $appAuthorizationSession;
    private SessionInterface $session;

    public function test_to_store_an_app_in_session(): void
    {
        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
            'redirect_uri' => 'http://shopware.example.com/callback',
            'authorization_scope' => 'read_catalog_structure write_products',
            'authentication_scope' => 'openid profile',
            'state' => 'foo',
        ]);
        $this->appAuthorizationSession->initialize($appAuthorization);

        $authorizationInSession = $this->session->get('_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715');
        Assert::assertNotEmpty($authorizationInSession);
        Assert::assertEquals([
            'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
            'authorization_scope' => 'read_catalog_structure write_products',
            'authentication_scope' => 'openid profile',
            'redirect_uri' => 'http://shopware.example.com/callback',
            'state' => 'foo',
        ], \json_decode($authorizationInSession, true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_to_get_an_app_in_session(): void
    {
        $this->session->set(
            '_app_auth_90741597-54c5-48a1-98da-a68e7ee0a715',
            \json_encode([
                'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'redirect_uri' => 'http://shopware.example.com/callback',
                'authorization_scope' => 'read_catalog_structure write_products',
                'authentication_scope' => 'openid profile',
                'state' => 'foo',
            ])
        );

        $authorizationInSession = $this->appAuthorizationSession->getAppAuthorization('90741597-54c5-48a1-98da-a68e7ee0a715');
        Assert::assertNotEmpty($authorizationInSession);
        Assert::assertInstanceOf(AppAuthorization::class, $authorizationInSession);
        Assert::assertEquals(
            [
                'client_id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'redirect_uri' => 'http://shopware.example.com/callback',
                'authorization_scope' => 'read_catalog_structure write_products',
                'authentication_scope' => 'openid profile',
                'state' => 'foo',
            ],
            $authorizationInSession->normalize()
        );
    }

    public function test_to_get_an_app_that_is_not_in_session(): void
    {
        $authorizationInSession = $this->appAuthorizationSession->getAppAuthorization('90741597-54c5-48a1-98da-a68e7ee0a715');
        Assert::assertNull($authorizationInSession);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->appAuthorizationSession = $this->get(AppAuthorizationSession::class);
        $this->session = $this->get('session');
    }
}
