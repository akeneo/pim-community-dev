<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage\FileHandler;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Move an uploaded file to the dropbox and save it to the database
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 *
 * TODO: could be moved in a dedicated FileStorage component
 */
class UploadedFileHandler extends AbstractFileHandler
{
    /**
     * {@inheritdoc}
     */
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
            sprintf('%s://%s', $this->srcFsAlias, $uploadedFile->getFilename()),
            sprintf('%s://%s', $this->destFsAlias, $file->getPathname())
        );

        $this->saver->save($file);

        return $file;
    }
}
