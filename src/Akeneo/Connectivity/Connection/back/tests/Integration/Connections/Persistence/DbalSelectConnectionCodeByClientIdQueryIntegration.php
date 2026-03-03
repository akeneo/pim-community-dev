<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Connections\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\Persistence\DbalSelectConnectionCodeByClientIdQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionCodeByClientIdQueryIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private DbalSelectConnectionCodeByClientIdQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->query = $this->get(DbalSelectConnectionCodeByClientIdQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_the_correct_connection_code(): void
    {
        $this->connectionLoader->createConnection('connectionA', 'Connection A', FlowType::DATA_DESTINATION, false);
        $connectionB = $this->connectionLoader->createConnection('connectionB', 'Connection B', FlowType::OTHER, false);
        $this->connectionLoader->createConnection('connectionC', 'Connection C', FlowType::OTHER, true, 'app');

        $connectionCode = $this->query->execute($connectionB->clientId());

        Assert::assertSame('connectionB', $connectionCode);
    }

    public function test_it_returns_null_when_client_id_does_not_match(): void
    {
        $connectionCode = $this->query->execute('wrong-client-id');

        Assert::assertNull($connectionCode);
    }
}
