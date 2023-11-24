<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use PHPUnit\Framework\Assert;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait ExecuteMigrationTrait
{
    /**
     * @return string
     */
    abstract protected function getParameter(string $parameter);

    /**
     * TODO on Master rebase:
     * This method should be renamed to "executeMigration", and the second parameter should be renamed to "reExecute".
     */
    private function reExecuteMigration(string $migrationLabel, bool $removePreviouslyExecutedMigration = false): void
    {
        $pathFinder = new PhpExecutableFinder();
        $phpCommand = $pathFinder->find();

        $rootDir = $this->getParameter('kernel.project_dir');

        if ($removePreviouslyExecutedMigration) {
            $this->checkMigrationIsIrreversible($phpCommand, $rootDir, $migrationLabel);
            $this->deleteMigration($phpCommand, $rootDir, $migrationLabel);
        }
        $this->innerExecuteMigration($phpCommand, $rootDir, $migrationLabel);
    }

    private function innerExecuteMigration(string $phpCommand, string $rootDir, string $migrationLabel): void
    {
        $output = [];
        $status = null;

        exec(
            sprintf(
                "%s %s/bin/console doctrine:migrations:execute 'Pim\Upgrade\Schema\Version%s' --up -n",
                $phpCommand,
                $rootDir,
                $migrationLabel
            ),
            $output,
            $status
        );
        Assert::assertEquals(0, $status, \json_encode($output));
    }

    private function deleteMigration(string $phpCommand, string $rootDir, string $migrationLabel): void
    {
        $output = [];
        $status = null;

        exec(
            sprintf(
                "%s %s/bin/console doctrine:migrations:version 'Pim\Upgrade\Schema\Version%s' --delete -n",
                $phpCommand,
                $rootDir,
                $migrationLabel
            ),
            $output,
            $status
        );
        Assert::assertEquals(0, $status, 'Migration were not executed.');
    }

    private function checkMigrationIsIrreversible(string $phpCommand, string $rootDir, string $migrationLabel): void
    {
        $output = [];
        $status = null;

        exec(
            sprintf(
                "%s %s/bin/console doctrine:migrations:execute 'Pim\Upgrade\Schema\Version%s' --down -n 2>&1",
                $phpCommand,
                $rootDir,
                $migrationLabel
            ),
            $output,
            $status
        );

        Assert::assertEquals(1, $status, 'Migration should be irreversible.');
    }
}
