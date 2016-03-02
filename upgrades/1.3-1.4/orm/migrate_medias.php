<?php

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once __DIR__ . '/../../../app/bootstrap.php.cache';
require_once __DIR__ . '/../../../app/AppKernel.php';
require_once __DIR__ . '/../../SchemaHelper.php';
require_once __DIR__ . '/../../UpgradeHelper.php';
require_once __DIR__ . '/../common/MediaMigration.php';

// TO BE LAUNCHED AFTER HAVING UPDATED THE DEPENDENCIES OF YOUR PROJECT
// NOT ABLE TO USE DOCTRINE ORM/MONGODB AS DEPENDENCIES HAVE BEEN UPDATED BUT NOT THE DATABASE AT THIS POINT
// (IE: DOCTRINE MAPPINGS HAVE CHANGED BUT NOT THE DATABASE)

/**********************************************
 * USAGE
 * migrate_medias.php [--env environment] [--product-media-table table] [--media-directory directory]
 * with:
 *      environment: your application environment (dev, prod...), default is dev
 *      table: the name of the table where your product medias are stored, default is pim_catalog_product_media
 *      directory: the directory where your product medias are located, default is %kernel.root_dir%/uploads/product/
 **********************************************/

$migration = new MediaMigration(new ConsoleOutput(), new ArgvInput($argv));

$mediaTable = $migration->getProductMediaTable();
$valueTable = $migration->getSchemaHelper()->getTableOrCollection('product_value');

$migration->createFileInfoTable();
$migration->storeLocalMedias();
$migration->setOriginalFilenameToMedias($valueTable, $mediaTable);
$migration->migrateMediasOnProductValue($valueTable, $mediaTable, 'FK_93A1BBF3EA9FDD75');
$migration->cleanFileInfoTable();
$migration->dropFormerMediaTable($mediaTable);
$migration->close();
