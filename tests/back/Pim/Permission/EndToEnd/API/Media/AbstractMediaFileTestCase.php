<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Media;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product\AbstractProductTestCase;

abstract class AbstractMediaFileTestCase extends AbstractProductTestCase
{
    private $filePaths = [];

    /**
     * @param \SplFileInfo $file
     */
    protected function createMedia(\SplFileInfo $file)
    {
        $fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
        $file = $fileStorer->store($file, FileStorage::CATALOG_STORAGE_ALIAS);
        $this->filePaths[] = $file->getKey();
    }

    /**
     * {@inheritdoc}
     *
     * Remove all files generated by tests
     */
    protected function tearDown(): void
    {
        $mountManager = $this->get('oneup_flysystem.mount_manager');
        $filesystem = $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);

        foreach ($this->filePaths as $pathFile) {
            if ($filesystem->has($pathFile)) {
                $filesystem->delete($pathFile);
            }
        }

        parent::tearDown();
    }
}
