<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\EndToEnd\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetSystemInformationEndToEnd extends ApiTestCase
{
    /**
     * @group ce
     */
    public function test_to_get_ce_system_information_through_the_api(): void
    {
        putenv('PIM_EDITION=community_edition_instance');

        $apiConnectionEcommerce = $this->createConnection('ecommerce', 'Ecommerce');
        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnectionEcommerce->clientId(),
            $apiConnectionEcommerce->secret(),
            $apiConnectionEcommerce->username(),
            $apiConnectionEcommerce->password()
        );

        $apiClient->request('GET', 'api/rest/v1/system-information');
        $response = $apiClient->getResponse();

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        Assert::assertCount(2, $content);
        Assert::assertEquals('CE', $content['edition']);
        Assert::assertMatchesRegularExpression('/^7.0.[0-9]*$/', $content['version']);
    }

    /**
     * @group ce
     */
    public function test_to_get_ge_system_information_through_the_api(): void
    {
        putenv('PIM_EDITION=growth_edition_instance');

        $apiConnectionEcommerce = $this->createConnection('ecommerce', 'Ecommerce');
        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnectionEcommerce->clientId(),
            $apiConnectionEcommerce->secret(),
            $apiConnectionEcommerce->username(),
            $apiConnectionEcommerce->password()
        );

        $apiClient->request('GET', 'api/rest/v1/system-information');
        $response = $apiClient->getResponse();

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        Assert::assertCount(2, $content);
        Assert::assertEquals('GE', $content['edition']);
        Assert::assertMatchesRegularExpression('/^7.0.[0-9]*$/', $content['version']);
    }

    /**
     * @group ce
     */
    public function test_to_get_ee_system_information_through_the_api(): void
    {
        putenv('PIM_EDITION=flexibility_instance');

        $apiConnectionEcommerce = $this->createConnection('ecommerce', 'Ecommerce');
        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnectionEcommerce->clientId(),
            $apiConnectionEcommerce->secret(),
            $apiConnectionEcommerce->username(),
            $apiConnectionEcommerce->password()
        );

        $apiClient->request('GET', 'api/rest/v1/system-information');
        $response = $apiClient->getResponse();

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        Assert::assertCount(2, $content);
        Assert::assertEquals('EE', $content['edition']);
        Assert::assertMatchesRegularExpression('/^7.0.[0-9]*$/', $content['version']);
    }

    /**
     * @group ce
     */
    public function test_to_get_serenity_system_information_through_the_api(): void
    {
        putenv('PIM_EDITION=serenity_instance');

        $apiConnectionEcommerce = $this->createConnection('ecommerce', 'Ecommerce');
        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnectionEcommerce->clientId(),
            $apiConnectionEcommerce->secret(),
            $apiConnectionEcommerce->username(),
            $apiConnectionEcommerce->password()
        );

        $apiClient->request('GET', 'api/rest/v1/system-information');
        $response = $apiClient->getResponse();

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        Assert::assertCount(2, $content);
        Assert::assertEquals('Serenity', $content['edition']);
        Assert::assertMatchesRegularExpression('/^7.0.[0-9]*$/', $content['version']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
