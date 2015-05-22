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
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Move a local file to the storage destination filesystem
 * transforms it as a \PimEnterprise\Component\ProductAsset\Model\FileInterface
 * and save it to the database.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 *
 * TODO: could be moved in a dedicated FileStorage component
 */
class LocalStorer extends AbstractRawFileStorer
{
    /**
     * {@inheritdoc}
     */
    public function store(\SplFileInfo $localFile, $destFsAlias)
    {
        $filesystem = $this->mountManager->getFilesystem($destFsAlias);
        $storageData = $this->pathGenerator->generate($localFile);

        $mimeType = MimeTypeGuesser::getInstance()->guess($localFile->getPathname());
        $size     = filesize($localFile->getPathname());

        $file = $this->createNewFile();
        $file->setFilename($storageData['file_name']);
        $file->setGuid($storageData['guid']);
        $file->setMimeType($mimeType);
        $file->setOriginalFilename($localFile->getFilename());
        $file->setPath($storageData['path']);
        $file->setSize($size);
        $file->setExtension($localFile->getExtension());
        $file->setStorage($destFsAlias);

        $resource = fopen($localFile->getPathname(), 'r');
        if (false === $filesystem->writeStream($file->getPathname(), $resource)) {
            throw new TransferFileException(
                sprintf('Unable to move the file "%s" to the "%s" filesystem.', $localFile->getPathname(), $destFsAlias)
            );
        }

        $this->saver->save($file);

        if (false === unlink($localFile->getPathname())) {
            throw new DeletionFileException(sprintf('Unable to delete the file "%s".', $localFile->getPathname()));
        }

        return $file;
    }
}
