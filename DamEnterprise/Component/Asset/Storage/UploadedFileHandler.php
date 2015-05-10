<?php

namespace DamEnterprise\Component\Asset\Storage;

use DamEnterprise\Component\Asset\AssetFileSystems;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileHandler extends AbstractFileHandler
{
    public function handle(\SplFileInfo $uploadedFile)
    {
        if (!$uploadedFile instanceof UploadedFile) {
            throw new \InvalidArgumentException(
                'This file handle only supports "Symfony\Component\HttpFoundation\File\UploadedFile".'
            );
        }

        $storageData = $this->pathGenerator->generate($uploadedFile);

        $file = $this->createNewFile();
        $file->setFilename($storageData['file_name']);
        $file->setGuid($storageData['guid']);
        $file->setMimeType($uploadedFile->getMimeType());
        $file->setOriginalFilename($uploadedFile->getClientOriginalName());
        $file->setPath($storageData['path']);
        $file->setSize($uploadedFile->getClientSize());

        $this->mountManager->move(
            sprintf('%s://%s', AssetFileSystems::FS_INCOMING_UPLOAD, $uploadedFile->getFilename()),
            sprintf('%s://%s', AssetFileSystems::FS_DROPBOX_AIRLOCK, $file->getPathname())
        );

        $this->saver->save($file);

        return $file;
    }
}
