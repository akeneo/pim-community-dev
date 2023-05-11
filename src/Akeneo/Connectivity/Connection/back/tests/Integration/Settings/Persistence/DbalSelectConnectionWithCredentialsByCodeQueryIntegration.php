<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Settings\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Settings\Persistence\DbalSelectConnectionWithCredentialsByCodeQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionWithCredentialsByCodeQueryIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private SelectConnectionWithCredentialsByCodeQueryInterface $selectConnectionWithCredentialsByCodeQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->selectConnectionWithCredentialsByCodeQuery = $this->get(DbalSelectConnectionWithCredentialsByCodeQuery::class);
    }

    public function test_it_finds_a_connection_with_its_credentials(): void
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION, false);

        $connection = $this->selectConnectionWithCredentialsByCodeQuery->execute('magento');

        Assert::assertInstanceOf(ConnectionWithCredentials::class, $connection);
        Assert::assertSame('magento', $connection->code());
        Assert::assertSame('Magento Connector', $connection->label());
        Assert::assertSame(FlowType::DATA_DESTINATION, $connection->flowType());
        Assert::assertNotNull($connection->clientId());
        Assert::assertNotNull($connection->secret());
        Assert::assertNotNull($connection->username());
        Assert::assertNull($connection->image());
        Assert::assertFalse($connection->auditable());
    }

    public function test_it_does_not_find_a_connection_when_the_code_does_exists(): void
    {
        $connection = $this->selectConnectionWithCredentialsByCodeQuery->execute('magento');

        Assert::assertNull($connection);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
