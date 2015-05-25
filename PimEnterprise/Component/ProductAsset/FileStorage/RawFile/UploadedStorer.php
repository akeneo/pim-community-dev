<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\FileStorage\RawFile;

use PimEnterprise\Component\ProductAsset\Exception\DeletionFileException;
use PimEnterprise\Component\ProductAsset\Exception\TransferFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Move a Symfony UploadedFile to the storage destination filesystem
 * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
 * and save it to the database.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 *
 * TODO: could be moved in a dedicated FileStorage component
 */
class UploadedStorer extends AbstractRawFileStorer
{
    /**
     * {@inheritdoc}
     */
    public function store(\SplFileInfo $uploadedFile, $destFsAlias)
    {
        if (!$uploadedFile instanceof UploadedFile) {
            throw new \InvalidArgumentException(
                'This raw file storer only supports "Symfony\Component\HttpFoundation\File\UploadedFile" files.'
            );
        }

        $filesystem = $this->mountManager->getFilesystem($destFsAlias);
        $storageData = $this->pathGenerator->generate($uploadedFile);

        $file = $this->createNewFile();
        $file->setFilename($storageData['file_name']);
        $file->setGuid($storageData['guid']);
        $file->setMimeType($uploadedFile->getMimeType());
        $file->setOriginalFilename($uploadedFile->getClientOriginalName());
        $file->setPath($storageData['path']);
        $file->setSize($uploadedFile->getClientSize());
        $file->setExtension($uploadedFile->getExtension());
        $file->setStorage($destFsAlias);

        $resource = fopen($uploadedFile->getPathname(), 'r');
        if (false === $filesystem->writeStream($file->getPathname(), $resource)) {
            throw new TransferFileException(
                sprintf(
                    'Unable to move the file "%s" to the "%s" filesystem.',
                    $uploadedFile->getPathname(),
                    $destFsAlias
                )
            );
        }

        $this->saver->save($file);

        if (false === unlink($uploadedFile->getPathname())) {
            throw new DeletionFileException(sprintf('Unable to delete the file "%s".', $uploadedFile->getPathname()));
        }

        return $file;
    }
}
