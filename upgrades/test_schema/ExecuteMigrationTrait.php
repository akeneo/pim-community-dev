<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Tool\Component\Console\CommandLauncher;
use PHPUnit\Framework\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait ExecuteMigrationTrait
{
    /**
     * @param string $service
     * @return mixed
     */
    abstract protected function get(string $service);

    private function getCommandLauncher(): CommandLauncher
    {
        return $this->get('pim_catalog.command_launcher');
    }

    private function reExecuteMigration(string $migrationLabel): void
    {
        $resultDown = $this->getCommandLauncher()->executeForeground(
            sprintf('doctrine:migrations:execute %s --down -n', $migrationLabel)
        );
        Assert::assertEquals(1, $resultDown->getCommandStatus(),'Migration should be irreversible.');

        $resultUp = $this->getCommandLauncher()->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', $migrationLabel)
        );
        Assert::assertEquals(0, $resultUp->getCommandStatus(), \json_encode($resultUp->getCommandOutput()));
    }
}
