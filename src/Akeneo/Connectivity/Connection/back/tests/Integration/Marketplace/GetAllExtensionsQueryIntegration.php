<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\Extension;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\GetAllExtensionsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetAllExtensionsQueryIntegration extends TestCase
{
    private GetAllExtensionsQuery $getAllExtensionsQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getAllExtensionsQuery = $this->get('akeneo_connectivity.connection.marketplace.get_all_extensions_query');
    }

    public function test_to_get_all_extensions()
    {
        $result = $this->getAllExtensionsQuery->execute();

        $this->assertEquals(120, $result->count());
        $this->assertEquals(Extension::create([
                'id' => '90741597-54c5-48a1-98da-a68e7ee0a715',
                'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-Connector" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The connector uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
                'certified' => false,
                'categories' => [
                    'E-commerce'
                ]
            ]),
            $result->extensions()[0]
        );
        $this->assertEquals(120, count($result->extensions()));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
