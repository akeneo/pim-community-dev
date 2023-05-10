<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Test\Acceptance\UseCases\PurgeInstance;

use Akeneo\Platform\Installer\Application\PurgeInstance\PurgeInstanceCommand;
use Akeneo\Platform\Installer\Application\PurgeInstance\PurgeInstanceHandler;
use Akeneo\Platform\Installer\Test\Acceptance\FakeServices\FakeDatabasePurger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PurgeInstanceTest extends KernelTestCase
{
    public function test_it_purges_the_pim(): void
    {
        $this->getHandler()->handle(new PurgeInstanceCommand());

        $this->assertTablesHaveBeenPurged([
            'akeneo_batch_job_instance',
            'akeneo_batch_job_execution',
            'akeneo_measurement',
            'migration_versions',
            'oro_config',
            'oro_config_value',
            'oro_user',
            'oro_user_access_group',
            'oro_user_access_group_role',
            'oro_user_access_role',
            'pim_catalog_product',
            'pim_catalog_product_model',
            'pim_comment_comment',
            'pim_configuration',
            'pim_session',
        ]);
    }

    private function assertTablesHaveBeenPurged(array $tableNames): void
    {
        $this->getDatabasePurger()->assertTablesHaveBeenPurged($tableNames);
    }

    private function getDatabasePurger(): FakeDatabasePurger
    {
        return self::getContainer()->get('Akeneo\Platform\Installer\Infrastructure\DatabasePurger\DbalPurger');
    }

    private function getHandler(): PurgeInstanceHandler
    {
        return self::getContainer()->get('Akeneo\Platform\Installer\Application\PurgeInstance\PurgeInstanceHandler');
    }
}
