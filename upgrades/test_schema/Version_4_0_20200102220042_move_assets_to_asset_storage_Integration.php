<?php
declare(strict_types=1);

namespace Pimee\Upgrade\Schema\Tests;


use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class Version_4_0_20200102220042_move_assets_to_asset_storage_Integration extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_migrates_file_from_asset_manager_from_catalog_storage_to_asset_storage()
    {
        $this->createFilesInCatalogStorage();
        $this->insertFileInfoEntries();
        $this->insertAssetsWithFiles();

        $resultUp = $this->get('pim_catalog.command_launcher')->executeForeground(
            sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel())
        );
        self::assertEquals(0, $resultUp->getCommandStatus(), \json_encode($resultUp->getCommandOutput()));

        $this->assertFileInfoEntriesUsesAssetStorage();
        $this->assertFilesInAssetStorage();
    }

    private function createFilesInCatalogStorage()
    {
        $mountManager = $this->get('oneup_flysystem.mount_manager');

        $catalogStorage = $mountManager->getFilesystem('catalogStorage');

        $catalogStorage->write('m/y/i/m/myimage1.jpg', 'content of myimage1.jpg');
        $catalogStorage->write('m/y/i/m/myimage2.jpg', 'content of myimage2.jpg');
        $catalogStorage->write('m/y/i/m/myimage3.jpg', 'content of myimage3.jpg');
        $catalogStorage->write('m/y/i/m/myimage4.jpg', 'content of myimage4.jpg');
        $catalogStorage->write('m/y/i/m/myimage5.jpg', 'content of myimage5.jpg');
    }

    private function insertFileInfoEntries()
    {
        $this->get('database_connection')->executeQuery(<<<SQL
            INSERT INTO akeneo_file_storage_file_info (file_key, original_filename, mime_type, extension, storage) VALUES
              ('m/y/i/m/myimage1.jpg', 'myimage1.jpg', 'image/jpeg', 'jpg', 'catalogStorage'),
              ('m/y/i/m/myimage2.jpg', 'myimage2.jpg', 'image/jpeg', 'jpg', 'catalogStorage'),
              ('m/y/i/m/myimage3.jpg', 'myimage3.jpg', 'image/jpeg', 'jpg', 'catalogStorage'),
              ('m/y/i/m/myimage4.jpg', 'myimage4.jpg', 'image/jpeg', 'jpg', 'catalogStorage'),
              ('m/y/i/m/myimage5.jpg', 'myimage5.jpg', 'image/jpeg', 'jpg', 'catalogStorage')
SQL
        );
    }

    private function insertAssetsWithFiles()
    {
        $this->get('database_connection')->executeQuery(<<<SQL
            INSERT INTO akeneo_asset_manager_asset_family(identifier, labels, rule_templates, transformations, naming_convention) VALUES
            ('f', '{}', '{}', '{}', '{}')
SQL
        );

        $stmt = $this->get('database_connection')->executeQuery(<<<SQL
            INSERT INTO akeneo_asset_manager_asset (identifier, code, asset_family_identifier, value_collection) VALUES
              ('a', 'a', 'f', '{"image": {"data": {"size": 1234, "filePath": "m/y/i/m/myimage1.jpg", "mimeType": "image/jpeg"}}}'),
              ('b', 'b', 'f', '{"image": {"data": {"size": 4567, "filePath": "m/y/i/m/myimage2.jpg", "mimeType": "image/jpeg"}}}'),
              ('c', 'c', 'f', '{"image": {"data": {"size": 7890, "filePath": "m/y/i/m/myimage3.jpg", "mimeType": "image/jpeg"}}}'),
              ('d', 'd', 'f', '{
                "image": {"data": {"size": 1234, "filePath": "m/y/i/m/myimage4.jpg", "mimeType": "image/jpeg"}},
                "other_image": {"data": {"size": 1234, "filePath": "m/y/i/m/myimage5.jpg", "mimeType": "image/jpeg"}}
              }'),
              ('e', 'e', 'f', '{}'),
              ('f', 'f', 'f', '{}')
SQL
        );
    }

    private function assertFileInfoEntriesUsesAssetStorage()
    {
        $stmt = $this->get('database_connection')->executeQuery(<<<SQL
            SELECT DISTINCT(storage) FROM akeneo_file_storage_file_info WHERE file_key IN
            ('m/y/i/m/myimage1.jpg', 'm/y/i/m/myimage2.jpg', 'm/y/i/m/myimage3.jpg', 'm/y/i/m/myimage4.jpg', 'm/y/i/m/myimage5.jpg')
SQL
        );

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        self::assertEquals(1, count($result));
        self::assertEquals([0 => ['storage' => 'assetStorage']], $result);
    }

    private function assertFilesInAssetStorage()
    {
        $mountManager = $this->get('oneup_flysystem.mount_manager');

        $assetStorage = $mountManager->getFilesystem('assetStorage');

        self::assertEquals('content of myimage1.jpg', $assetStorage->read('m/y/i/m/myimage1.jpg'));
        self::assertEquals('content of myimage2.jpg', $assetStorage->read('m/y/i/m/myimage2.jpg'));
        self::assertEquals('content of myimage3.jpg', $assetStorage->read('m/y/i/m/myimage3.jpg'));
        self::assertEquals('content of myimage4.jpg', $assetStorage->read('m/y/i/m/myimage4.jpg'));
        self::assertEquals('content of myimage5.jpg', $assetStorage->read('m/y/i/m/myimage5.jpg'));
    }

    protected function tearDown(): void
    {
        $mountManager = $this->get('oneup_flysystem.mount_manager');
        $assetStorage = $mountManager->getFilesystem('assetStorage');
        $catalogStorage = $mountManager->getFilesystem('catalogStorage');

        for ($i = 0; $i <= 5; $i ++) {
            $path = "m/y/i/m/myimage$i.jpg";

            if ($assetStorage->has($path)) {
                $assetStorage->delete($path);
            }
            if ($catalogStorage->has($path)) {
                $catalogStorage->delete($path);
            }
        }
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
