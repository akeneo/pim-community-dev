<?php

namespace PimEnterprise\Component\ProductAsset\FileStorage\FileHandler;

use PimEnterprise\Component\ProductAsset\FileStorage\ProductAssetFileSystems;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class ImportedFileHandler extends AbstractFileHandler
{
    public function handle(\SplFileInfo $importedFile)
    {
        $storageData = $this->pathGenerator->generate($importedFile);

        $mimeType = MimeTypeGuesser::getInstance()->guess($importedFile->getPathname());
        $size = filesize($importedFile->getPathname());

        $file = $this->createNewFile();
        $file->setFilename($storageData['file_name']);
        $file->setGuid($storageData['guid']);
        $file->setMimeType($mimeType);
        $file->setOriginalFilename($importedFile->getFilename());
        $file->setPath($storageData['path']);
        $file->setSize($size);

        $this->mountManager->move(
            sprintf('%s://%s', ProductAssetFileSystems::FS_INCOMING_IMPORT, $importedFile->getPathname()),
            sprintf('%s://%s', ProductAssetFileSystems::FS_DROPBOX_AIRLOCK, $file->getPathname())
        );

        $this->saver->save($file);

        return $file;
    }
}
