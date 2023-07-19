<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Installer\Persistence\Sql;

use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\GetInstallDatetime;
use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\InstallData;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    JM Leroux <jmleroux.pro@gmail.com>
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetInstallDatetimeIntegration extends TestCase
{
    public function test_it_returns_null_if_not_installed(): void
    {
        $installDatetime = $this->getQuery()->__invoke();

        $this->assertNull($installDatetime);
    }

    public function test_it_gets_install_datetime(): void
    {
        $installDataQuery = $this->get(InstallData::class);
        $installDataQuery->withDatetime(new \DateTimeImmutable('2022-12-13'));

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

    private function getQuery(): GetInstallDatetime
    {
        return $this->get(GetInstallDatetime::class);
    }
}
