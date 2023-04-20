<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\CommandExecutor;

use Akeneo\Platform\Installer\Domain\CommandExecutor\CreateTableInterface;

final class CreateTable extends AbstractCommandExecutor implements CreateTableInterface
{
    public function getName(): string
    {
        return 'doctrine:schema:update';
    }
}
