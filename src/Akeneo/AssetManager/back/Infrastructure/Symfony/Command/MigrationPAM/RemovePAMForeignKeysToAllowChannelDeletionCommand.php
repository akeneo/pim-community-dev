<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\MigrationPAM;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// TODO @merge DO NOT PULLUP THIS CLASS IN 5.0 BECAUSE THERE IS A MIGRATION TO DELETE FOREIGN KEYS IN THIS BRANCH
class RemovePAMForeignKeysToAllowChannelDeletionCommand extends Command
{
    protected static $defaultName = 'pimee:assets:migrate:remove-foreign-keys-to-allow-channel-deletion';

    /** @var Connection */
    private $connection;

    /** @var SymfonyStyle */
    private $io;
    /**
     * @var string
     */
    private $databaseName;

    public function __construct(Connection $connection, string $databaseName)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->databaseName = $databaseName;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->deleteForeignKeys();

        return 0;
    }

    private function deleteForeignKeys(): void
    {
        $removedForeignKeys = false;

        if ($this->foreignKeyExists('pimee_product_asset_channel_variation_configuration', 'FK_FF39199B72F5A1AA')) {
            $sql = <<<SQL
ALTER TABLE pimee_product_asset_channel_variation_configuration
DROP FOREIGN KEY FK_FF39199B72F5A1AA;
SQL;

            $this->connection->executeUpdate($sql);
            $removedForeignKeys = true;
            $this->io->text('Successfully removed foreign key FK_FF39199B72F5A1AA for table pimee_product_asset_channel_variation_configuration');
        }

        if ($this->foreignKeyExists('pimee_product_asset_variation', 'FK_F895CD872F5A1AA')) {
            $sql = <<<SQL
ALTER TABLE pimee_product_asset_variation
DROP FOREIGN KEY FK_F895CD872F5A1AA;
SQL;

            $this->connection->executeUpdate($sql);
            $removedForeignKeys = true;
            $this->io->text('Successfully removed foreign key FK_F895CD872F5A1AA for table pimee_product_asset_variation');
        }

        if ($removedForeignKeys === true) {
            $this->io->success('Success! Channel deletion is possible, now.');
        } else {
            $this->io->text('Nothing has to be updated.');
        }
    }

    private function foreignKeyExists(string $tableName, string $foreignKeyName): bool
    {
        $sql = <<<SQL
SELECT EXISTS (
   SELECT *
   FROM information_schema.KEY_COLUMN_USAGE
   WHERE TABLE_SCHEMA = :databaseName
   AND TABLE_NAME = :tableName
   AND CONSTRAINT_NAME = :foreignKeyName
)
SQL;
        $exists = $this->connection->executeQuery($sql,
            [
                'databaseName' => $this->databaseName,
                'tableName' => $tableName,
                'foreignKeyName' => $foreignKeyName,
            ]
        )->fetchColumn();

        return (bool)$exists;
    }
}
