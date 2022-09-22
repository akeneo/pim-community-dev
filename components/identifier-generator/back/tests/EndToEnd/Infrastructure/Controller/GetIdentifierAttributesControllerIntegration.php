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
    public function it_should_redirect_on_non_xml_request(): void
    {
        $this->getAuthenticated()->logIn('Julia', $this->client);
        $this->getWebClientHelper()->callRoute(
            $this->client,
            'akeneo_identifier_generator_get_identifier_attributes',
            [],
            'GET',
            [
                'HTTP_X-Requested-With' => 'toto'
            ]
        );
        $response = $this->client->getResponse();
        Assert::AssertSame(Response::HTTP_FOUND, $response->getStatusCode());
        Assert::assertTrue($response->isRedirect('/'));
    }

    /**
     * @test
     */
    public function it_should_get_identifiers(): void
    {
        $this->getAuthenticated()->logIn('Julia', $this->client);
        $this->getWebClientHelper()->callRoute(
            $this->client,
            'akeneo_identifier_generator_get_identifier_attributes',
            [],
            'GET',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertSame('[{"code":"sku","labels":{"en_US":"[sku]"}}]', $response->getContent());
    }

    /**
     * @test
     */
    public function it_should_be_unauthorized(): void
    {
        $this->getAuthenticated()->logIn('admin', $this->client);
        $this->disableAcl('action:pim_enrich_attribute_index');
        $this->getWebClientHelper()->callRoute(
            $this->client,
            'akeneo_identifier_generator_get_identifier_attributes',
            [],
            'GET',
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest'
            ]
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
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
