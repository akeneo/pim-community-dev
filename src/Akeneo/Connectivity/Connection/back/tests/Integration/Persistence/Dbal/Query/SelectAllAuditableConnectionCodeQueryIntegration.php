<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\SelectAllAuditableConnectionCodeQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SelectAllAuditableConnectionCodeQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var SelectAllAuditableConnectionCodeQuery */
    private $selectAuditableConnectionsCodeQuery;

    public function test_it_selects_only_auditable_connections_code()
    {
        $this->connectionLoader->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $this->connectionLoader->createConnection('translation', 'Translation', FlowType::OTHER, true);
        $this->connectionLoader->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION, false);

        $codes = $this->selectAuditableConnectionsCodeQuery->execute();
        Assert::assertCount(2, $codes);
        sort($codes);
        Assert::assertEquals('erp', $codes[0]);
        Assert::assertEquals('translation', $codes[1]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->selectAuditableConnectionsCodeQuery = $this->get('akeneo_connectivity_connection.persistence.query.select_all_auditable_connection_code');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
