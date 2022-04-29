<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_7_0_20220429131804_execute_uuid_migration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Execute migration command to migrate from id to uuid';
    }

    public function up(Schema $schema): void
    {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'pim:product:migrate-to-uuid',
            '--with-stats' => true,
        ]);

        $application->run($input, new NullOutput());
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
