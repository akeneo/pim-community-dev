<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Migration;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Injects the service container into the migrations implementing ContainerAwareMigrationInterface.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ContainerAwareMigrationFactory implements MigrationFactory
{
    public function __construct(
        private readonly MigrationFactory $migrationFactory,
        private readonly ContainerInterface $container,
    ) {
    }

    public function createVersion(string $migrationClassName): AbstractMigration
    {
        $migration = $this->migrationFactory->createVersion($migrationClassName);

        if ($migration instanceof ContainerAwareMigrationInterface) {
            $migration->setContainer($this->container);
        }

        return $migration;
    }
}
