<?php

namespace Pim\Upgrade\Schema;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * This migration will migrate the user avatars from the old field 'image' to the 'file_info_id'
 */
class Version_3_0_20180717082452_migrate_avatars extends AbstractMigration implements ContainerAwareInterface
{
    private const USERS_TABLE = 'oro_user';

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileRemovalException
     * @throws FileTransferException
     * @throws \Exception
     */
    public function up(Schema $schema)
    {
        $this->disableMigrationWarning();

        $fileStorer = $this->container->get('akeneo_file_storage.file_storage.file.file_storer');
        $query = sprintf(
            'SELECT id, image FROM %s WHERE image IS NOT NULL AND file_info_id IS NULL',
            self::USERS_TABLE
        );
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $usersWithAvatar = $stmt->fetchAll();

        $rootDir = $this->container->get('kernel')->getProjectDir();
        $uploadDir = sprintf(
            '%s%s%s%suploads%susers',
            $rootDir,
            DIRECTORY_SEPARATOR,
            'web',
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        if (is_dir($uploadDir)) {
            foreach ($usersWithAvatar as $userWithAvatar) {
                $userId = $userWithAvatar['id'];
                $fileName = $userWithAvatar['image'];

                $finder = new Finder();
                $finder->files()->in($uploadDir)->name($fileName);
                if ($finder->count()) {
                    $iterator = $finder->getIterator();
                    $iterator->rewind();
                    $firstFile = $iterator->current();

                    if ($firstFile) {
                        $file = $fileStorer->store($firstFile, FileStorage::CATALOG_STORAGE_ALIAS);
                        $this->connection->update(
                            self::USERS_TABLE,
                            [
                                'file_info_id' => $file->getId(),
                                'image' => null,
                            ],
                            ['id' => $userId]
                        );
                    } else {
                        $this->write(sprintf(
                            '<comment>File "%s" is not a file!</comment>',
                            $fileName
                        ));
                    }
                } else {
                    $this->write(sprintf(
                        '<comment>No matching file "%s" found</comment>',
                        $fileName
                    ));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws IrreversibleMigrationException
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }
}
