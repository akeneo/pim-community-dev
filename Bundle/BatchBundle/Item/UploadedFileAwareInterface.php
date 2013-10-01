<?php

namespace Oro\Bundle\BatchBundle\Item;

use Symfony\Component\HttpFoundation\File\UploadedFile;

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
    public function setUploadedFile(UploadedFile $uploadedFile);
}
