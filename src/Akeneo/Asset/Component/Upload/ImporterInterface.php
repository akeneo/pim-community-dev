<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Upload;

/**
 * Import uploaded assets files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface ImporterInterface
{
    /**
     * Import all uploaded files
     *
     * @param UploadContext $uploadContext
     * @param array         $fileNames
     *
     * @return array
     *
     * @todo Add a method to import by file names and remove the optional parameter $fileNames.
     */
    public function import(UploadContext $uploadContext, array $fileNames = []);

    /**
     * @param UploadContext $uploadContext
     *
     * @return \SplFileInfo[]
     */
    public function getImportedFiles(UploadContext $uploadContext);


    /**
     * @param UploadContext $uploadContext
     * @param array         $fileNames
     *
     * @return \SplFileInfo[]
     */
    public function getImportedFilesFromNames(UploadContext $uploadContext, array $fileNames);
}
