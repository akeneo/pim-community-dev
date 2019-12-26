<?php

declare(strict_types=1);

namespace Akeneo\Apps\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Apps\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Apps\Domain\Settings\Model\Read\Connection;
use Akeneo\Apps\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Settings\Persistence\Query\SelectConnectionsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionsQueryIntegration extends TestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var SelectConnectionsQuery */
    private $selectConnectionsQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_app.fixtures.connection_loader');
        $this->selectConnectionsQuery = $this->get('akeneo_app.persistence.query.select_connections');
    }

    public function test_it_fetches_connections()
    {
        $this->connectionLoader->createConnection('magento', 'Magento Connector', FlowType::DATA_DESTINATION);
        sleep(1);
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::OTHER);

        $connections = $this->selectConnectionsQuery->execute();

        Assert::assertCount(2, $connections);
        Assert::assertContainsOnlyInstancesOf(Connection::class, $connections);
        Assert::assertSame('magento', $connections[0]->code());
        Assert::assertSame('bynder', $connections[1]->code());
    }

    public function test_it_fetches_without_connection()
    {
        $connections = $this->selectConnectionsQuery->execute();

        Assert::assertCount(0, $connections);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
