<?php

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\Infrastructure\Controller;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use Akeneo\Test\Pim\Automation\IdentifierGenerator\EndToEnd\ControllerEndToEndTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

final class GetIdentifierAttributesControllerIntegration extends ControllerEndToEndTestCase
{
    /**
     * @test
     */
    public function it_should_be_authenticated(): void
    {
        $this->getWebClientHelper()->callApiRoute(
            $this->client,
            'akeneo_identifier_generator_get_identifier_attributes'
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_should_get_identifiers(): void
    {
        $this->getAuthenticated()->logIn('Julia', $this->client);
        $this->getWebClientHelper()->callApiRoute(
            $this->client,
            'akeneo_identifier_generator_get_identifier_attributes'
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertSame('[{"code":"sku","labels":{"en_US":"[sku]"}}]', $response->getContent());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getWebClientHelper(): WebClientHelper {
        /** @var WebClientHelper $webClientHelper */
        $webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');

        return $webClientHelper;
    }

    private function getAuthenticated(): AuthenticatorHelper
    {
        /** @var AuthenticatorHelper $authenticatorHelper */
        $authenticatorHelper = $this->get('akeneo_integration_tests.helper.authenticator');

        return $authenticatorHelper;
    }
}
