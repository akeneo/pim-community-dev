<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ListConnectionEndToEnd extends WebTestCase
{
    public function test_it_lists_connections(): void
    {
        $this->createConnection('franklin', 'Franklin', FlowType::DATA_SOURCE);
        $this->createConnection('dam', 'DAM', FlowType::OTHER);

        $this->authenticateAsAdmin();
        $this->client->request('GET', '/rest/connections');
        $result = json_decode($this->client->getResponse()->getContent(), true);

        $expectedResult = [
            [
                'code' => 'franklin',
                'label' => 'Franklin',
                'flowType' => FlowType::DATA_SOURCE,
                'image' => null
            ],
            [
                'code' => 'dam',
                'label' => 'DAM',
                'flowType' => FlowType::OTHER,
                'image' => null
            ]
        ];

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
