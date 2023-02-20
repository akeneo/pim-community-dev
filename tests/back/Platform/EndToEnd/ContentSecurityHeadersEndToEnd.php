<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\EndToEnd;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ContentSecurityHeadersEndToEnd extends WebTestCase
{
    /**
     * @group ce
     */
    public function test_csp_headers(): void
    {
        $this->authenticateAsAdmin();

        $this->client->request(
            'GET',
            '/',
        );

        $response = $this->client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $cspHeaders = $response->headers->get('content-security-policy');

        Assert::assertStringContainsString("default-src 'self' 'unsafe-inline';", $cspHeaders);
        Assert::assertStringContainsString(
            "img-src 'self' data: apps.akeneo.com marketplace.akeneo.com;",
            $cspHeaders
        );
        Assert::assertMatchesRegularExpression(
            "/script-src 'self' 'unsafe-eval' 'nonce-[a-z0-9]+';/",
            $cspHeaders
        );
        Assert::assertStringContainsString("frame-src *;", $cspHeaders);
        Assert::assertStringContainsString("font-src 'self' data:;", $cspHeaders);
        Assert::assertStringContainsString("connect-src 'self'", $cspHeaders);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
