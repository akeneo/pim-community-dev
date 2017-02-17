<?php

use Pim\Component\Catalog\FileStorage;
use Pim\Upgrade\SchemaHelper;
use Pim\Upgrade\UpgradeHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Finder\Finder;

/**
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaMigration
{
    const MEDIA_DIR = '/uploads/product/';
    const MEDIA_TABLE = 'pim_catalog_product_media';

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    /** @var \Doctrine\DBAL\Driver\Connection */
    protected $ormConnection;

    /** @var \Doctrine\MongoDB\Connection */
    protected $mongoConnection;

    /** @var SchemaHelper */
    protected $schemaHelper;

    /** @var UpgradeHelper */
    protected $upgradeHelper;

    /** @var ConsoleOutput */
    protected $output;

    /** @var string */
    protected $productMediaTable;

    /** @var string */
    protected $mediaDirectory;

    /**
     * @param ConsoleOutput $output
     * @param ArgvInput     $input
     */
    public function __construct(ConsoleOutput $output, ArgvInput $input)
    {
        $this->output = $output;

        $kernel = $this->bootKernel($input->getParameterOption(['-e', '--env'], 'dev'));
        $this->container = $kernel->getContainer();
        $this->ormConnection = $this->container->get('database_connection');
        $this->schemaHelper = new SchemaHelper($this->container);
        $this->upgradeHelper = new UpgradeHelper($this->container);

        $this->mediaDirectory = $input->getParameterOption(
            ['--media-directory'],
            $this->container->getParameter('kernel.root_dir') . self::MEDIA_DIR
        );

        $this->productMediaTable = $input->getParameterOption(['--product-media-table'], self::MEDIA_TABLE);

        if (!is_dir($this->mediaDirectory)) {
            throw new \RuntimeException(sprintf('The media directory "%s" does not exist', $this->mediaDirectory));
        }

        if ($this->upgradeHelper->areProductsStoredInMongo()) {
            $this->mongoConnection = $this->container->get('doctrine_mongodb.odm.default_connection');
        }
    }

    /**
     * Create the akeneo_file_storage_file_info table with temporary fields to ease the migration.
     */
    public function createFileInfoTable()
    {
        $this->output->writeln('Creating table <comment>akeneo_file_storage_file_info</comment>...');
        $this->ormConnection->exec('CREATE TABLE akeneo_file_storage_file_info (id INT AUTO_INCREMENT NOT NULL, file_key VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, size INT DEFAULT NULL, extension VARCHAR(10) NOT NULL, hash VARCHAR(100) DEFAULT NULL, storage VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_F19B3719A5D32530 (file_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->output->writeln('Adding temporary fields to table <comment>akeneo_file_storage_file_info</comment>...');
        $this->ormConnection->exec('ALTER TABLE akeneo_file_storage_file_info ADD old_file_key VARCHAR(255)');
        $this->ormConnection->exec('CREATE UNIQUE INDEX old_file_key ON akeneo_file_storage_file_info (old_file_key)');
    }

    /**
     * Store each file located in the directory to the
     * catalog filesystem in the table akeneo_file_storage_file_info.
     *
     * At the end of this method, all local files are stored in the new filesystem,
     * and for each "new" media, we know the identifier of the "old" media
     */
    public function storeLocalMedias()
    {
        /** @var \Doctrine\ORM\EntityManagerInterface $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $this->output->writeln(sprintf('Storing medias located in <comment>%s</comment> to the catalog filesystem...', $this->mediaDirectory));

        $storer = $this->container->get('akeneo_file_storage.file_storage.file.file_storer');

        $finder = new Finder();
        $count = 0;
        $batch = 1000;
        foreach ($finder->files()->followLinks()->in($this->mediaDirectory) as $file) {
            $fileInfo = $storer->store($file, FileStorage::CATALOG_STORAGE_ALIAS);
            $this->ormConnection->update(
                'akeneo_file_storage_file_info',
                ['old_file_key' => $file->getFilename()],
                ['id'           => $fileInfo->getId()]
            );
            $em->detach($fileInfo);
            unset($fileInfo);
            unset($file);

            if (0 == $count % $batch) {
                $em->clear();
            }
            $count++;
        }

        $em->clear();
    }

    /**
     * Set back the original filename to medias.
     *
     * @param string $productValueTable
     * @param string $productMediaTable
     */
    public function setOriginalFilenameToMedias($productValueTable, $productMediaTable)
    {
        $this->output->writeln('Setting original filenames to medias files...');
        if ($this->upgradeHelper->areProductsStoredInMongo()) {
            $this->setOriginalFilenameToMediasMongo($productValueTable);
        } else {
            $this->setOriginalFilenameToMediasOrm($productMediaTable);
        }
    }

    /**
     * Link files to the product values.
     *
     * @param string $productValueTable
     * @param string $productMediaTable
     * @param string $fkMedia
     */
    public function migrateMediasOnProductValue($productValueTable, $productMediaTable, $fkMedia)
    {
        if ($this->upgradeHelper->areProductsStoredInMongo()) {
            $this->migrateMediasOnProductValueMongo($productValueTable);
        } else {
            $this->migrateMediasOnProductValueOrm($productValueTable, $productMediaTable, $fkMedia);
        }
    }

    /**
     * Remove temporary fields to akeneo_file_storage_file_info
     */
    public function cleanFileInfoTable()
    {
        $this->output->writeln('Removing temporary fields to table <comment>akeneo_file_storage_file_info</comment>...');
        $this->ormConnection->exec('ALTER TABLE akeneo_file_storage_file_info DROP old_file_key');
    }

    /**
     * Remove old media table
     *
     * @param string $productMediaTable
     */
    public function dropFormerMediaTable($productMediaTable)
    {
        $this->output->writeln(sprintf('Dropping table <comment>%s</comment>...', $productMediaTable));
        $this->ormConnection->exec(sprintf('DROP TABLE %s', $productMediaTable));
    }

    /**
     * End migration
     */
    public function close()
    {
        $this->output->writeln('');
        $this->output->writeln('<info>Done!</info>');
    }

    /**
     * @return mixed
     */
    public function getProductMediaTable()
    {
        return $this->productMediaTable;
    }

    /**
     * @return SchemaHelper
     */
    public function getSchemaHelper()
    {
        return $this->schemaHelper;
    }

    /**
     * Set back the original filename to Mongo medias.
     *
     * @param string $productTable
     */
    protected function setOriginalFilenameToMediasMongo($productTable)
    {
        $db = $this->getMongoDatabase();
        $valueCollection = new MongoCollection($db, $productTable);

        $productsWithMedia = $valueCollection->find(['values.media' => ['$ne' => null]]);

        $stmt = $this->ormConnection->prepare(
            'UPDATE akeneo_file_storage_file_info fi
            SET fi.original_filename = ?
            WHERE fi.old_file_key = ?'
        );

        foreach ($productsWithMedia as $product) {
            foreach ($product['values'] as $value) {
                if (isset($value['media'])) {
                    $stmt->bindValue(1, $value['media']['originalFilename']);
                    $stmt->bindValue(2, $value['media']['filename']);
                    $stmt->execute();
                }
            }
        }
    }

    /**
     * Set back the original filename to ORM medias.
     *
     * @param string $productMediaTable
     */
    protected function setOriginalFilenameToMediasOrm($productMediaTable)
    {
        $this->ormConnection->exec(sprintf(
            'UPDATE akeneo_file_storage_file_info fi
            INNER JOIN %s pm ON fi.old_file_key = pm.filename
            SET fi.original_filename = pm.original_filename',
            $productMediaTable
        ));
    }

    /**
     * Link files to the ORM product values.
     *
     * @param string $productValueTable
     * @param string $productMediaTable
     * @param string $fkMedia
     */
    protected function migrateMediasOnProductValueOrm($productValueTable, $productMediaTable, $fkMedia)
    {
        $this->output->writeln(sprintf('Adding temporary fields to table <comment>%s</comment>...', $productValueTable));
        $this->ormConnection->exec(sprintf('ALTER TABLE %s ADD new_media_id INT(11) NULL DEFAULT NULL AFTER media_id, ADD INDEX (new_media_id)', $productValueTable));

        // associate the "new" media ID to the product value
        //
        // UPDATE pim_catalog_product_value pv
        // LEFT JOIN pim_catalog_product_media pm ON pv.media_id = pm.id
        // LEFT JOIN akeneo_file_storage_file_info fi ON fi.old_file_key = pm.filename
        // SET pv.new_media_id=fi.id
        // WHERE pv.media_id IS NOT NULL
        $this->ormConnection->exec(sprintf(
            'UPDATE %s pv
            LEFT JOIN %s pm ON pv.media_id = pm.id
            LEFT JOIN akeneo_file_storage_file_info fi ON fi.old_file_key = pm.filename
            SET pv.new_media_id=fi.id
            WHERE pv.media_id IS NOT NULL',
            $productValueTable,
            $productMediaTable
        ));

        $this->output->writeln(sprintf('Cleaning temporary fields to table <comment>%s</comment>...', $productValueTable));
        $this->ormConnection->exec(sprintf('ALTER TABLE %s DROP FOREIGN KEY %s', $productValueTable, $fkMedia));
        $this->ormConnection->exec(sprintf('ALTER TABLE %s DROP media_id', $productValueTable));
        $this->ormConnection->exec(sprintf('ALTER TABLE %s CHANGE new_media_id media_id INT(11) NULL DEFAULT NULL, ADD INDEX (media_id)', $productValueTable));
        $this->ormConnection->exec(sprintf('ALTER TABLE %s ADD FOREIGN KEY (media_id) REFERENCES akeneo_file_storage_file_info(id) ON DELETE SET NULL ON UPDATE RESTRICT', $productValueTable));
        $this->ormConnection->exec(sprintf('DROP INDEX new_media_id ON %s', $productValueTable));
    }

    /**
     * Link files to the Mongo product values.
     *
     * @param string $productTable
     */
    protected function migrateMediasOnProductValueMongo($productTable)
    {
        $db = $this->getMongoDatabase();
        $valueCollection = new MongoCollection($db, $productTable);

        $productsWithMedia = $valueCollection->find(['values.media' => ['$ne' => null]]);

        $stmt = $this->ormConnection->prepare('SELECT fi.id FROM akeneo_file_storage_file_info fi WHERE fi.old_file_key = ?');

        foreach ($productsWithMedia as $product) {
            foreach ($product['values'] as $index => $value) {
                if (isset($value['media'])) {
                    $stmt->bindValue(1, $value['media']['filename']);
                    $stmt->execute();
                    $fileInfo = $stmt->fetch();

                    /*
                     db.pim_catalog_product.update(
                          { _id: ObjectId("55ee950c48177e12588b5ccb"), "values._id": ObjectId("55ee950c48177e12588b5cd4") },
                          { $set: {"values.$.media": NumberLong(666)} }
                     )
                     */
                    $valueCollection->update(
                        ['_id'  => new MongoId($product['_id']), 'values._id' => new MongoId($value['_id'])],
                        ['$set' => ['values.$.media' => (int)$fileInfo['id']]]
                    );
                }
            }
        }
    }

    /**
     * Boot kernel
     *
     * @param string $env
     *
     * @return AppKernel
     */
    protected function bootKernel($env)
    {
        $kernel = new AppKernel($env, $env === 'dev');
        $kernel->loadClassCache();
        $kernel->boot();

        return $kernel;
    }

    /**
     * @return MongoDB
     */
    protected function getMongoDatabase()
    {
        $dbName = $this->container->getParameter('mongodb_database');

        return $this->mongoConnection->getMongoClient()->$dbName;
    }
}
