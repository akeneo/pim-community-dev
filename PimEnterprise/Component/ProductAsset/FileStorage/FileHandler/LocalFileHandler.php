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

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Move a local file to the dropbox and save it to the database
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 *
 * TODO: could be moved in a dedicated FileStorage component
 */
class LocalFileHandler extends AbstractFileHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(\SplFileInfo $localFile)
    {
        $storageData = $this->pathGenerator->generate($localFile);

        $mimeType = MimeTypeGuesser::getInstance()->guess($localFile->getPathname());
        $size = filesize($localFile->getPathname());

        $file = $this->createNewFile();
        $file->setFilename($storageData['file_name']);
        $file->setGuid($storageData['guid']);
        $file->setMimeType($mimeType);
        $file->setOriginalFilename($localFile->getFilename());
        $file->setPath($storageData['path']);
        $file->setSize($size);

        $this->mountManager->move(
            //TODO: $localFile->getPathname() is wrong, maybe we can't use an \SplFileInfo as input of the method
            sprintf('%s://%s', $this->srcFsAlias, $localFile->getPathname()),
            sprintf('%s://%s', $this->destFsAlias, $file->getPathname())
        );

        $this->saver->save($file);

        return $file;
    }
}
