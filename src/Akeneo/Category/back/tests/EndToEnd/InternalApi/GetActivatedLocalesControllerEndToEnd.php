<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\EndToEnd\InternalApi;

use Akeneo\Category\back\tests\EndToEnd\Helper\ControllerIntegrationTestCase;
use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetActivatedLocalesControllerEndToEnd extends ControllerIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->logAs('julia');
    }

    public function testItGetActiveLocales(): void
    {
        $this->addLocale('yy_YY', true);
        $this->addLocale('zz_ZZ', false);

        $this->callApiRoute(
            client: $this->client,
            route: 'internal_api_category_catalog_activated_locales',
            method: Request::METHOD_GET,
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        // locale 'en_US' is present in DB by default
        $this->assertEqualsCanonicalizing(
            ['en_US', 'yy_YY'],
            json_decode($response->getContent(), true),
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function addLocale(string $code, bool $isActivated): void
    {
        $sql = <<<SQL
            INSERT INTO pim_catalog_locale (code, is_activated) VALUES (:code, :isActivated);
        SQL;

        $this->get('database_connection')->executeQuery(
            $sql,
            ['code' => $code, 'isActivated' => $isActivated],
            ['code' => \PDO::PARAM_STR, 'isActivated' => \PDO::PARAM_BOOL],
        );
    }
}
