<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\AuditLoader;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query\DbalSelectEventDatesToRefreshQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectEventDatesToRefreshQueryIntegration extends TestCase
{
    /** @var AuditLoader */
    private $auditLoader;

    /** @var DbalSelectEventDatesToRefreshQuery */
    private $selectEventDatesToRefreshQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditLoader = $this->get('akeneo_connectivity.connection.fixtures.audit_loader');
        $this->selectEventDatesToRefreshQuery = $this->get('akeneo_connectivity_connection.persistence.query.select_event_dates_to_refresh');
    }

    public function test_it_fetches_dates_to_refresh()
    {
        $this->auditLoader->insertData('magento', new \DateTimeImmutable('-1 day'), 510, EventTypes::PRODUCT_UPDATED);
        $this->auditLoader->insertData('magento', new \DateTimeImmutable('+2 day'), 32, EventTypes::PRODUCT_UPDATED);
        $this->auditLoader->insertData('bynder', new \DateTimeImmutable('+3 day'), 213, EventTypes::PRODUCT_UPDATED);

        $result = $this->selectEventDatesToRefreshQuery->execute();

        $expectedResult = [
            (new \DateTimeImmutable('+2 day'))->format('Y-m-d'),
            (new \DateTimeImmutable('+3 day'))->format('Y-m-d'),
        ];

        Assert::assertSame($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
