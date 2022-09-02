<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\ServiceAPI\Messenger;

use Akeneo\Catalogs\ServiceAPI\Command\CommandInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CommandBusInterface
{
    public function execute(CommandInterface $command): void;
}
