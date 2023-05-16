<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Marketplace\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetWebMarketplaceUrlEndToEnd extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_the_web_marketplace_url_for_the_current_user(): void
    {
        $this->authenticateAsAdmin();
        $this->client->xmlHttpRequest('GET', '/rest/marketplace/marketplace-url');
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertMatchesRegularExpression(
            '/https:\/\/apps\.akeneo\.com\/.*/',
            $result,
        );
    }
}
