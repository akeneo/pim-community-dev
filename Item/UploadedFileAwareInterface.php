<?php

namespace Akeneo\Bundle\BatchBundle\Item;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Interface for reader with uploaded file
 *
 */
interface UploadedFileAwareInterface
{
    /**
     * Get uploaded file constraints
     */
    public function getUploadedFileConstraints();

    /**
     * Set uploaded file
     *
     * @param UploadedFile $uploadedFile
     */
    public function setUploadedFile(File $uploadedFile);
}
