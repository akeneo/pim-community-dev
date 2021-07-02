<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Marketplace;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllExtensionsEndToEnd extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_all_the_extensions(): void
    {
        $extensions = [
            [
                'id' => '6fec7055-36ad-4301-9889-46c46ddd446a',
                'name' => 'Extension 1',
                'logo' => 'https://marketplace.test/logo/extension_1.png',
                'author' => 'Partner 1',
                'partner' => 'Akeneo Partner',
                'description' => 'Our Akeneo Connector',
                'url' => 'https://marketplace.test/extension/extension_1',
                'categories' => ['E-commerce'],
                'certified' => false
            ],
            [
                'id' => '896ae911-e877-46a0-b7c3-d7c572fe39ed',
                'name' => 'Extension 2',
                'logo' => 'https://marketplace.test/logo/extension_2.png',
                'author' => 'Partner 2',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'Our Akeneo Connector',
                'url' => 'https://marketplace.test/extension/extension_2',
                'categories' => ['E-commerce', 'Print'],
                'certified' => true
            ]
        ];

        // TODO: Persist extensions.

        $this->client->request('GET', '/rest/marketplace/extensions');
        $result = json_decode($this->client->getResponse()->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals(
            [
                'total' => 2,
                'extensions' => $extensions
            ],
            $result,
        );
    }
}
