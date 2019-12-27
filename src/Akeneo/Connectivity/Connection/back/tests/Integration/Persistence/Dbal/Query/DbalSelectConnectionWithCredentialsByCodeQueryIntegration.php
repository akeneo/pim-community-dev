<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQuery;
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
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var SelectConnectionWithCredentialsByCodeQuery */
    private $selectConnectionWithCredentialsByCodeQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->selectConnectionWithCredentialsByCodeQuery = $this->get('akeneo_connectivity.connection.persistence.query.select_connection_with_credentials_by_code');
    }

    public function test_it_finds_a_connection_with_its_credentials()
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION);

        $connection = $this->selectConnectionWithCredentialsByCodeQuery->execute('magento');

        Assert::assertInstanceOf(ConnectionWithCredentials::class, $connection);
        Assert::assertSame('magento', $connection->code());
        Assert::assertSame('Magento Connector', $connection->label());
        Assert::assertSame(FlowType::DATA_DESTINATION, $connection->flowType());
        Assert::assertNotNull($connection->clientId());
        Assert::assertNotNull($connection->secret());
        Assert::assertNotNull($connection->username());
        Assert::assertNull($connection->image());
    }

    public function test_it_does_not_find_a_connection_from_its_code()
    {
        $connection = $this->selectConnectionWithCredentialsByCodeQuery->execute('magento');

        Assert::assertNull($connection);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
