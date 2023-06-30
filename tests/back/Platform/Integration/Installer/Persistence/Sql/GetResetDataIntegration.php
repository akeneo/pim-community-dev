<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Installer\Persistence\Sql;

use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\GetResetData;
use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\InstallData;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetResetDataIntegration extends TestCase
{
    public function test_it_returns_if_not_reset(): void
    {
        $resetData = $this->getQuery()->__invoke();

        $this->assertNull($resetData);
    }

    public function test_it_gets_install_datetime(): void
    {
        $intallDataQuery = $this->get(InstallData::class);
        $intallDataQuery->withDatetime(new \DateTimeImmutable('2022-12-13'));

        $installDatetime = $this->getQuery()->__invoke();

        $this->assertInstanceOf(\DateTime::class, $installDatetime);
        $this->assertSame('2022-12-13 00:00:00', $installDatetime->format('Y-m-d H:i:s'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): GetResetData
    {
        return $this->get(GetResetData::class);
    }
}
