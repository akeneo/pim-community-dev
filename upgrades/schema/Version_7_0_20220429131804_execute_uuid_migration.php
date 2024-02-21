<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class Version_7_0_20220429131804_execute_uuid_migration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Execute command to migrate from id to uuid';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SELECT "disable migration warning"');

        $kernel = new \Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'pim:product:migrate-to-uuid',
            '--wait-for-dqi' => false,
            '--lock-tables' => true,
        ]);
        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (Command::SUCCESS !== $exitCode) {
            throw new \Exception(sprintf('Migration failed: %s', $output->fetch()));
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
