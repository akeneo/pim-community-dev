<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\ZddMigrations;

use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class V20221205153905FillIdentifierPrefixesZddMigration implements ZddMigration
{

    public function migrate(): void
    {
        // TODO: Implement migrate() method.
    }

    public function getName(): string
    {
        return 'FillIdentifierPrefixes';
    }
}
