<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectErrorCountPerConnectionQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectErrorCountPerConnectionQueryIntegration extends TestCase
{
    /** @var SelectErrorCountPerConnectionQuery */
    private $selectErrorCountPerConnectionQuery;

    public function test_it_gets_error_count_per_connection()
    {
        $errorCountPerConnection = $this->selectAuditableConnectionsCodeQuery->execute();
        Assert::assertCount(2, $errorCountPerConnection);
//        sort($codes);
        Assert::assertEquals('erp', $errorCountPerConnection[0][]);
        Assert::assertEquals('translation', $errorCountPerConnection[1]['connection_code']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->selectErrorCountPerConnectionQuery = $this->get('akeneo_connectivity_connection.persistence.query.select_error_count_per_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertErrorCount(): void
    {

    }
}
