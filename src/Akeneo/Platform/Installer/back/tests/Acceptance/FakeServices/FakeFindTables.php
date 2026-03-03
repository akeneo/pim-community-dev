<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Installer\Test\Acceptance\FakeServices;

use Akeneo\Platform\Installer\Domain\Query\FindTablesInterface;

class FakeFindTables implements FindTablesInterface
{
    public function all(): array
    {
        return [
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
        ];
    }
}
