<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Connections\WrongCredentialsCombination\Persistence;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Persistence\DbalAreCredentialsValidCombinationQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalAreCredentialsValidCombinationQueryIntegration extends TestCase
{
    private ConnectionLoader $connectionLoader;
    private DbalAreCredentialsValidCombinationQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->query = $this->get(DbalAreCredentialsValidCombinationQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_credentials_are_valid_combination(): void
    {
        $this->connectionLoader->createConnection('connectionA', 'Connection A', FlowType::DATA_DESTINATION, false);
        $connectionB = $this->connectionLoader->createConnection('connectionB', 'Connection B', FlowType::OTHER, false);
        $this->connectionLoader->createConnection('connectionC', 'Connection C', FlowType::OTHER, true, 'app');

        $areCredentialsValidCombination = $this->query->execute($connectionB->clientId(), $connectionB->username());

        Assert::assertTrue($areCredentialsValidCombination);
    }

    public function test_credentials_are_not_valid_combination(): void
    {
        $this->connectionLoader->createConnection('connectionA', 'Connection A', FlowType::DATA_DESTINATION, false);

        $areCredentialsValidCombination = $this->query->execute('wrong-client-id', 'connectionA');

        Assert::assertFalse($areCredentialsValidCombination);
    }
}
