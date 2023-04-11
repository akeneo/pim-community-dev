<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Application\InstallDatabase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InstallDatabaseCommand
{
    public function __construct(
        public readonly string $catalogPath,
        public readonly bool $withElasticSearchIndexes,
    ) {
    }
}
